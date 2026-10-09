<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Cas;
use App\Models\Rrhh\EstudioAcademico;
use App\Models\Rrhh\ExperienciaLaboral;
use App\Models\Rrhh\FichaPersonal;
use App\Models\Rrhh\Persona;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Rrhh\PlanillaExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PersonalController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly PlanillaExcelImportService $excelImportService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $query = Persona::with(['user', 'asignacionesPuestos.puesto.unidadOrganizacional', 'fichaPersonal.datosLaborales']);

        if ($request->boolean('solo_disponibles')) {
            $query->whereDoesntHave('asignacionesPuestos', function ($q) {
                $q->where('_estado', 'ACTIVO')->whereNull('fecha_fin');
            });
        }

        if ($search) {
            $s = strtolower(trim((string) $search));
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(nombres) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER(segundo_apellido) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER(nro_documento) LIKE ?', ["%{$s}%"]);
            });
        }

        $personas = $query->orderBy('id', 'desc')->paginate((int) $request->query('per_page', 15));

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
            'nro_documento' => 'required|string|max:50|unique:pgsql.rrhh.personas,nro_documento',
            'correo_electronico_personal' => 'nullable|string|email|max:255',
            'telefono_celular' => 'nullable|string|max:50',
            'genero' => 'nullable|string|in:MASCULINO,FEMENINO,M,F,Masculino,Femenino,m,f,OTRO,Otro',
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
            $credencialesGeneradas = null;
            $persona = DB::transaction(function () use ($request, &$credencialesGeneradas) {
                $generoRaw = strtoupper(trim((string) $request->input('genero')));
                $genero = match ($generoRaw) {
                    'M', 'MASCULINO' => 'MASCULINO',
                    'F', 'FEMENINO' => 'FEMENINO',
                    'OTRO' => 'OTRO',
                    default => $generoRaw ?: null,
                };

                $persona = Persona::create([
                    'nombres' => strtoupper(trim((string) $request->input('nombres'))),
                    'primer_apellido' => $request->input('primer_apellido') ? strtoupper(trim((string) $request->input('primer_apellido'))) : null,
                    'segundo_apellido' => $request->input('segundo_apellido') ? strtoupper(trim((string) $request->input('segundo_apellido'))) : null,
                    'tipo_documento' => $request->input('tipo_documento', 'CI'),
                    'nro_documento' => trim((string) $request->input('nro_documento')),
                    'fecha_nacimiento' => $request->input('fecha_nacimiento') ?: null,
                    'correo_electronico_personal' => $request->input('correo_electronico_personal') ?: null,
                    'telefono_celular' => $request->input('telefono_celular') ?: null,
                    'genero' => $genero,
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

                // Crear usuario ERP si fue solicitado (Patrón institucional: Ej. PQJ4589201 y Pqj4589201!!)
                if ($request->boolean('crear_usuario')) {
                    $creds = self::generarCredencialesIniciales($persona);
                    $baseLogin = $request->input('usuario_login') ? Str::slug($request->input('usuario_login'), '') : $creds['usuario'];
                    $passwordPlana = $creds['password'];

                    $candidate = $baseLogin;
                    $count = 1;
                    while (User::where('usr_usuario', $candidate)->exists()) {
                        $candidate = $baseLogin . $count;
                        $count++;
                    }
                    $login = $candidate;

                    User::create([
                        'name' => $persona->nombre_completo,
                        'usr_usuario' => $login,
                        'password' => Hash::make($passwordPlana),
                        'email' => $persona->correo_electronico_personal ?: null,
                        'usr_externo_id' => $persona->id,
                        'usr_estado' => 'A',
                    ]);

                    $credencialesGeneradas = [
                        'usuario' => $login,
                        'password' => $passwordPlana,
                    ];
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
                'credenciales' => $credencialesGeneradas,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar funcionario', ['exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al registrar funcionario: ' . $ex->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $persona = Persona::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:100',
            'primer_apellido' => 'nullable|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'nro_documento' => "required|string|max:50|unique:pgsql.rrhh.personas,nro_documento,{$id}",
            'correo_electronico_personal' => 'nullable|string|email|max:255',
            'telefono_celular' => 'nullable|string|max:50',
            'genero' => 'nullable|string|in:MASCULINO,FEMENINO,M,F,Masculino,Femenino,m,f,OTRO,Otro',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $generoRaw = strtoupper(trim((string) $request->input('genero')));
            $genero = match ($generoRaw) {
                'M', 'MASCULINO' => 'MASCULINO',
                'F', 'FEMENINO' => 'FEMENINO',
                'OTRO' => 'OTRO',
                default => $generoRaw ?: null,
            };

            $oldValues = $persona->toArray();

            $persona->update([
                'nombres' => strtoupper(trim((string) $request->input('nombres'))),
                'primer_apellido' => $request->input('primer_apellido') ? strtoupper(trim((string) $request->input('primer_apellido'))) : null,
                'segundo_apellido' => $request->input('segundo_apellido') ? strtoupper(trim((string) $request->input('segundo_apellido'))) : null,
                'nro_documento' => trim((string) $request->input('nro_documento')),
                'fecha_nacimiento' => $request->input('fecha_nacimiento') ?: null,
                'correo_electronico_personal' => $request->input('correo_electronico_personal') ?: null,
                'telefono_celular' => $request->input('telefono_celular') ?: null,
                'genero' => $genero,
                'observacion' => $request->input('observacion'),
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            $this->auditService->log(
                event: 'persona_updated',
                model: $persona,
                oldValues: $oldValues,
                newValues: $persona->fresh()->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Datos del funcionario actualizados correctamente.',
                'data' => $persona->fresh(),
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar funcionario', ['exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar funcionario: ' . $ex->getMessage(),
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

        if (! $personaId) {
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

        $estudio = EstudioAcademico::create([
            'id_ficha_personal' => $fichaId,
            'institucion' => strtoupper(trim((string) $request->input('institucion'))),
            'carrera' => strtoupper(trim((string) $request->input('carrera'))),
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

        $exp = ExperienciaLaboral::create([
            'id_ficha_personal' => $fichaId,
            'empresa_institucion' => strtoupper(trim((string) $request->input('empresa_institucion'))),
            'cargo' => strtoupper(trim((string) $request->input('cargo'))),
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

        $cas = Cas::create([
            'id_ficha_personal' => $fichaId,
            'nro_resolucion' => trim((string) $request->input('nro_resolucion')),
            'anios' => (int) $request->input('anios', 0),
            'meses' => (int) $request->input('meses', 0),
            'dias' => (int) $request->input('dias', 0),
            'fecha_emision' => $request->input('fecha_emision'),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Certificación CAS añadida al legajo.', 'data' => $cas], Response::HTTP_CREATED);
    }

    /**
     * Genera credenciales sugeridas según estándar institucional:
     * Usuario: Iniciales mayúsculas (P. Apellido + S. Apellido + 1er Nombre) + CI (Ej: Perez Quispe Juan -> PQJ4589201)
     * Contraseña: Mismo acrónimo en formato título + CI + "!!" (Ej: Pqj4589201!!)
     */
    public static function generarCredencialesIniciales(Persona $persona): array
    {
        $p1 = !empty($persona->primer_apellido) ? mb_substr(trim($persona->primer_apellido), 0, 1) : '';
        $p2 = !empty($persona->segundo_apellido) ? mb_substr(trim($persona->segundo_apellido), 0, 1) : '';

        $nombres = trim((string) $persona->nombres);
        $primerNombre = preg_split('/\s+/', $nombres)[0] ?? 'U';
        $p3 = !empty($primerNombre) ? mb_substr($primerNombre, 0, 1) : 'U';

        $iniciales = mb_strtoupper($p1 . $p2 . $p3);
        if (empty($iniciales)) {
            $iniciales = 'USR';
        }

        // Limpiar documento (quitar caracteres especiales y espacios)
        $docLimpio = preg_replace('/[^A-Za-z0-9]/', '', (string) $persona->nro_documento);
        if (empty($docLimpio)) {
            $docLimpio = (string) $persona->id;
        }

        $usuario = $iniciales . $docLimpio;

        // Formato contraseña institucional (Ej: Pqj4589201!!)
        $initTitle = mb_strtoupper(mb_substr($iniciales, 0, 1)) . mb_strtolower(mb_substr($iniciales, 1));
        $password = $initTitle . $docLimpio . '!!';

        return [
            'usuario' => $usuario,
            'password' => $password,
        ];
    }

    /**
     * Crear cuenta de usuario ERP para un funcionario existente que no tenga cuenta.
     */
    public function crearUsuarioErp(Request $request, int $id): JsonResponse
    {
        $persona = Persona::findOrFail($id);

        if (User::where('usr_externo_id', $persona->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El funcionario ya cuenta con un usuario ERP vinculado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $creds = self::generarCredencialesIniciales($persona);
        $login = $request->input('usuario_login') ? Str::slug($request->input('usuario_login'), '') : $creds['usuario'];
        $passwordPlana = $creds['password'];

        $candidate = $login;
        $count = 1;
        while (User::where('usr_usuario', $candidate)->exists()) {
            $candidate = $login . $count;
            $count++;
        }
        $login = $candidate;

        $user = User::create([
            'name' => $persona->nombre_completo,
            'usr_usuario' => $login,
            'password' => Hash::make($passwordPlana),
            'email' => $persona->correo_electronico_personal ?: null,
            'usr_externo_id' => $persona->id,
            'usr_estado' => 'A',
        ]);

        $this->auditService->log(
            event: 'user_created_for_persona',
            model: $user,
            newValues: ['usr_usuario' => $login, 'persona_id' => $persona->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Usuario ERP creado exitosamente con el estándar institucional.',
            'credenciales' => [
                'nombre' => $persona->nombre_completo,
                'usuario' => $login,
                'password' => $passwordPlana,
            ],
            'user' => $user,
        ]);
    }

    /**
     * Restablecer contraseña de un funcionario según el estándar institucional.
     */
    public function resetPasswordErp(Request $request, int $id): JsonResponse
    {
        $persona = Persona::findOrFail($id);
        $user = User::where('usr_externo_id', $persona->id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'El funcionario no tiene una cuenta de usuario ERP vinculada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $creds = self::generarCredencialesIniciales($persona);
        $passwordPlana = $creds['password'];

        $user->update([
            'password' => Hash::make($passwordPlana),
            'usr_estado' => 'A',
        ]);

        $this->auditService->log(
            event: 'user_password_reset',
            model: $user,
            newValues: ['usr_usuario' => $user->usr_usuario]
        );

        return response()->json([
            'success' => true,
            'message' => 'Contraseña restablecida exitosamente al estándar institucional.',
            'credenciales' => [
                'nombre' => $persona->nombre_completo,
                'usuario' => $user->usr_usuario,
                'password' => $passwordPlana,
            ],
        ]);
    }

    /**
     * Previsualiza los funcionarios y datos detectados en el archivo Excel de Planilla.
     */
    public function previsualizarExcelPlanilla(Request $request): JsonResponse
    {
        $filePath = $this->resolverRutaArchivoExcel($request);

        if (!$filePath || !file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el archivo Excel de planilla para procesar. Suba un archivo o seleccione el archivo oficial del sistema.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $data = $this->excelImportService->previsualizar($filePath);
            return response()->json($data, Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error al previsualizar planilla Excel: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo Excel: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Importa y sincroniza el personal, cargos y escalas salariales desde el Excel.
     */
    public function importarExcelPlanilla(Request $request): JsonResponse
    {
        $filePath = $this->resolverRutaArchivoExcel($request);

        if (!$filePath || !file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el archivo Excel de planilla para sincronizar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $userId = auth()->id() ? (int) auth()->id() : 1;
            $res = $this->excelImportService->importar($filePath, $userId);

            $this->auditService->log(
                event: 'rrhh_personal_excel_imported',
                model: new Persona(),
                newValues: $res['estadisticas'] ?? []
            );

            return response()->json($res, Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error al importar personal desde Excel: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error durante la migración: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resuelve la ruta del archivo Excel (archivo subido o archivo oficial en servidor).
     */
    private function resolverRutaArchivoExcel(Request $request): ?string
    {
        if ($request->hasFile('archivo_excel')) {
            $file = $request->file('archivo_excel');
            if ($file->isValid()) {
                return $file->getRealPath();
            }
        }

        // Buscar archivo oficial de planilla en rutas estándar del servidor
        $rutas = [
            base_path('../PLANILLA DE SUELDOS SEPTIEMBRE.xlsx'),
            base_path('PLANILLA DE SUELDOS SEPTIEMBRE.xlsx'),
            '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/PLANILLA DE SUELDOS SEPTIEMBRE.xlsx',
        ];

        foreach ($rutas as $r) {
            if (file_exists($r)) {
                return $r;
            }
        }

        return null;
    }
}

