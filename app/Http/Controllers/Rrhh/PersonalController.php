<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\FichaPersonal;
use App\Models\Rrhh\Persona;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PersonalController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $query = Persona::with(['user', 'asignacionesPuestos.puesto.unidadOrganizacional', 'fichaPersonal']);

        if ($search) {
            $s = strtolower(trim((string)$search));
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(nombres) LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('LOWER(segundo_apellido) LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('LOWER(nro_documento) LIKE ?', ["%{$s}%"]);
            });
        }

        $personas = $query->orderBy('id', 'desc')->paginate((int)$request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $personas->items(),
            'total' => $personas->total(),
            'current_page' => $personas->currentPage(),
            'last_page' => $personas->lastPage(),
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:100',
            'primer_apellido' => 'nullable|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'nro_documento' => 'required|string|max:50|unique:rrhh.personas,nro_documento',
            'correo_electronico_personal' => 'nullable|email|max:255',
            'telefono_celular' => 'nullable|string|max:50',
            'genero' => 'nullable|string|in:MASCULINO,FEMENINO',
            'crear_usuario' => 'nullable|boolean',
            'usuario_login' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $persona = DB::transaction(function () use ($request) {
                $persona = Persona::create([
                    'nombres' => strtoupper(trim((string)$request->input('nombres'))),
                    'primer_apellido' => $request->input('primer_apellido') ? strtoupper(trim((string)$request->input('primer_apellido'))) : null,
                    'segundo_apellido' => $request->input('segundo_apellido') ? strtoupper(trim((string)$request->input('segundo_apellido'))) : null,
                    'tipo_documento' => $request->input('tipo_documento', 'CI'),
                    'nro_documento' => trim((string)$request->input('nro_documento')),
                    'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                    'correo_electronico_personal' => $request->input('correo_electronico_personal'),
                    'telefono_celular' => $request->input('telefono_celular'),
                    'genero' => $request->input('genero'),
                    'observacion' => $request->input('observacion'),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // Crear ficha personal automáticamente
                FichaPersonal::create([
                    'id_persona' => $persona->id,
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // Crear usuario ERP si fue solicitado
                if ($request->input('crear_usuario')) {
                    $login = $request->input('usuario_login') ?: strtolower(substr((string)$persona->nombres, 0, 1) . $persona->primer_apellido);
                    User::create([
                        'name' => $persona->nombre_completo,
                        'usr_usuario' => $login,
                        'password' => Hash::make('123456'),
                        'email' => $persona->correo_electronico_personal,
                        'usr_externo_id' => $persona->id,
                        'usr_estado' => 'A',
                    ]);
                }

                $this->auditService->log(
                    event: 'persona_created',
                    model: $persona,
                    newValues: $persona->toArray()
                );

                return $persona;
            });

            return response()->json([
                'success' => true,
                'message' => 'Funcionario registrado exitosamente.',
                'data' => $persona,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar funcionario', ['exception' => $ex->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al registrar funcionario.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        $persona = Persona::with([
            'fichaPersonal.datosLaborales',
            'fichaPersonal.estudiosAcademicos',
            'fichaPersonal.experienciasLaborales',
            'fichaPersonal.cas',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) {
                $q->orderBy('fecha', 'desc')->limit(30);
            },
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $persona,
        ], Response::HTTP_OK);
    }

    public function miFichaPersonal(): JsonResponse
    {
        $user = auth()->user();
        $personaId = $user ? $user->usr_externo_id : null;

        if (!$personaId) {
            $persona = Persona::with(['fichaPersonal.datosLaborales', 'fichaPersonal.estudiosAcademicos', 'fichaPersonal.experienciasLaborales', 'fichaPersonal.cas', 'asignacionesPuestos.puesto.unidadOrganizacional'])->first();
        } else {
            $persona = Persona::with(['fichaPersonal.datosLaborales', 'fichaPersonal.estudiosAcademicos', 'fichaPersonal.experienciasLaborales', 'fichaPersonal.cas', 'asignacionesPuestos.puesto.unidadOrganizacional'])->find($personaId);
        }

        return response()->json([
            'success' => true,
            'data' => $persona,
        ], Response::HTTP_OK);
    }

    public function storeEstudio(Request $request, int $id): JsonResponse
    {
        $persona = Persona::with('fichaPersonal')->findOrFail($id);
        $fichaId = $persona->fichaPersonal ? $persona->fichaPersonal->id : FichaPersonal::create(['id_persona' => $id])->id;

        $estudio = \App\Models\Rrhh\EstudioAcademico::create([
            'id_ficha_personal' => $fichaId,
            'institucion' => strtoupper(trim((string)$request->input('institucion'))),
            'carrera' => strtoupper(trim((string)$request->input('carrera'))),
            'nivel_instruccion' => $request->input('nivel_instruccion', 'LICENCIATURA'),
            'fecha_emision' => $request->input('fecha_emision'),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Estudio académico añadido al legajo.', 'data' => $estudio], Response::HTTP_CREATED);
    }

    public function storeExperiencia(Request $request, int $id): JsonResponse
    {
        $persona = Persona::with('fichaPersonal')->findOrFail($id);
        $fichaId = $persona->fichaPersonal ? $persona->fichaPersonal->id : FichaPersonal::create(['id_persona' => $id])->id;

        $exp = \App\Models\Rrhh\ExperienciaLaboral::create([
            'id_ficha_personal' => $fichaId,
            'empresa_institucion' => strtoupper(trim((string)$request->input('empresa_institucion'))),
            'cargo' => strtoupper(trim((string)$request->input('cargo'))),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'motivo_retiro' => $request->input('motivo_retiro'),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Experiencia laboral añadida al legajo.', 'data' => $exp], Response::HTTP_CREATED);
    }

    public function storeCas(Request $request, int $id): JsonResponse
    {
        $persona = Persona::with('fichaPersonal')->findOrFail($id);
        $fichaId = $persona->fichaPersonal ? $persona->fichaPersonal->id : FichaPersonal::create(['id_persona' => $id])->id;

        $cas = \App\Models\Rrhh\Cas::create([
            'id_ficha_personal' => $fichaId,
            'nro_resolucion' => trim((string)$request->input('nro_resolucion')),
            'anios' => (int)$request->input('anios', 0),
            'meses' => (int)$request->input('meses', 0),
            'dias' => (int)$request->input('dias', 0),
            'fecha_emision' => $request->input('fecha_emision'),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Certificación CAS añadida al legajo.', 'data' => $cas], Response::HTTP_CREATED);
    }
}
