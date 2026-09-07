<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\PermisoDerivacion;
use App\Models\Correspondencia\UsuarioSecretario;
use App\Models\Correspondencia\Ventanilla;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermisosCorrespondenciaController extends Controller
{
    /**
     * Listado general de permisos
     */
    public function derivaciones(Request $request): JsonResponse
    {
        $idUnidadOrigen = $request->input('id_unidad_origen');
        $query = PermisoDerivacion::with(['unidadOrigen', 'unidadDestino'])
            ->where('_estado', 'ACTIVO');

        if ($idUnidadOrigen) {
            $query->where('id_origen', $idUnidadOrigen)->where('tipo_origen', 'UNIDAD');
        }

        return response()->json(['success' => true, 'data' => $query->get()], Response::HTTP_OK);
    }

    /**
     * Obtener listado de sujetos para selección según el tipo de pestaña (UNIDAD, CARGO, VENTANILLA, GESTOR)
     */
    public function listaGrupos(Request $request, string $tipo): JsonResponse
    {
        $filtro = $request->input('filtro');
        $idRegional = $request->input('id_regional');

        $filas = [];

        switch (strtoupper($tipo)) {
            case 'UNIDAD':
            case 'UNIDADES':
                $query = UnidadOrganizacional::with('regional')
                    ->where('_estado', 'ACTIVO');
                if ($idRegional) {
                    $query->where('id_regional', $idRegional);
                }
                if ($filtro) {
                    $query->where('nombre', 'ILIKE', "%{$filtro}%");
                }
                $filas = $query->orderBy('nombre')->get()->map(fn ($u) => [
                    'id' => (string) $u->id,
                    'nombre' => $u->nombre,
                    'codigo' => $u->sigla ?? 'U-'.$u->id,
                    'regional' => $u->regional?->nombre ?? 'Nacional',
                    'tipo' => 'UNIDAD',
                ]);
                break;

            case 'CARGO':
            case 'CARGOS':
            case 'USUARIO':
            case 'USUARIOS':
                $query = Puesto::with(['unidadOrganizacional.regional', 'asignacionesActivas.persona'])
                    ->where('_estado', 'ACTIVO');
                if ($idRegional) {
                    $query->whereHas('unidadOrganizacional', fn ($q) => $q->where('id_regional', $idRegional));
                }
                if ($filtro) {
                    $query->where(function ($q) use ($filtro) {
                        $q->where('nombre', 'ILIKE', "%{$filtro}%")
                            ->orWhereHas('asignacionesActivas.persona', function ($qp) use ($filtro) {
                                $qp->where('nombres', 'ILIKE', "%{$filtro}%")
                                    ->orWhere('primer_apellido', 'ILIKE', "%{$filtro}%");
                            });
                    });
                }
                $filas = $query->orderBy('nombre')->get()->map(function ($p) {
                    $persona = $p->asignacionesActivas->first()?->persona;
                    $nomPersona = $persona ? "{$persona->nombres} {$persona->primer_apellido}" : 'Puesto Vacante';

                    return [
                        'id' => (string) $p->id,
                        'nombre' => "{$p->nombre} ({$nomPersona})",
                        'cargo' => $p->nombre,
                        'funcionario' => $nomPersona,
                        'unidad' => $p->unidadOrganizacional?->nombre ?? '',
                        'regional' => $p->unidadOrganizacional?->regional?->nombre ?? 'Nacional',
                        'tipo' => 'CARGO',
                    ];
                });
                break;

            case 'VENTANILLA':
            case 'VENTANILLAS':
                $query = Ventanilla::with('regional')->where('_estado', 'ACTIVO');
                if ($idRegional) {
                    $query->where('id_regional', $idRegional);
                }
                if ($filtro) {
                    $query->where('nombre', 'ILIKE', "%{$filtro}%");
                }
                $filas = $query->orderBy('nombre')->get()->map(fn ($v) => [
                    'id' => (string) $v->id,
                    'nombre' => $v->nombre,
                    'codigo' => $v->codigo ?? 'VENT-'.$v->id,
                    'regional' => $v->regional?->nombre ?? 'Sede Central',
                    'tipo' => 'VENTANILLA',
                ]);
                break;

            case 'GESTOR_CORRESPONDENCIA':
            case 'GESTOR':
                $query = UsuarioSecretario::with(['user.persona', 'puestoTitular.unidadOrganizacional'])
                    ->where('_estado', 'ACTIVO');
                $filas = $query->get()->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'nombre' => "{$s->user?->persona?->nombres} {$s->user?->persona?->primer_apellido} [Asistente de: {$s->puestoTitular?->nombre}]",
                    'tipo' => 'GESTOR_CORRESPONDENCIA',
                ]);
                break;

            case 'VENTANILLA_DIGITAL':
                $filas = [
                    [
                        'id' => 'VENTANILLA_DIGITAL_01',
                        'nombre' => 'Ventanilla Única Digital (Portal Ciudadano Web)',
                        'tipo' => 'VENTANILLA_DIGITAL',
                    ],
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'filas' => $filas,
                'total' => count($filas),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Retorna el árbol institucional completo (Regionales > Unidades > Puestos) con formato para Treeview
     */
    public function arbolJerarquico(Request $request): JsonResponse
    {
        $regionales = Regional::with(['unidades' => function ($qu) {
            $qu->where('_estado', 'ACTIVO')->with(['puestos' => function ($qp) {
                $qp->where('_estado', 'ACTIVO')->with('asignacionesActivas.persona');
            }]);
        }])->where('_estado', 'ACTIVO')->orderBy('id')->get();

        $coloresRegionales = [
            1 => '#1976D2', // Azul La Paz
            2 => '#388E3C', // Verde Santa Cruz
            3 => '#F57C00', // Naranja Cochabamba
            4 => '#7B1FA2', // Púrpura Chuquisaca
            5 => '#C2185B', // Rosa Oruro
            6 => '#00796B', // Teal Potosí
            7 => '#E64A19', // Rojo Tarija
            8 => '#5D4037', // Café Beni
            9 => '#455A64', // Gris Pando
        ];

        $tree = [];

        foreach ($regionales as $reg) {
            $regColor = $coloresRegionales[$reg->id] ?? '#4F46E5';
            $regNode = [
                'id' => "REG-{$reg->id}",
                'name' => "Sede Regional: {$reg->nombre}",
                'tipo' => 'REGIONAL',
                'color' => $regColor,
                'children' => [],
            ];

            foreach ($reg->unidades as $u) {
                $unitNode = [
                    'id' => "U-{$u->id}",
                    'name' => $u->nombre,
                    'tipo' => 'UNIDAD',
                    'raw_id' => $u->id,
                    'sigla' => $u->sigla,
                    'color' => $regColor,
                    'children' => [],
                ];

                foreach ($u->puestos as $p) {
                    $persona = $p->asignacionesActivas->first()?->persona;
                    $nomPersona = $persona ? "{$persona->nombres} {$persona->primer_apellido}" : 'Vacante';

                    $unitNode['children'][] = [
                        'id' => "C-{$p->id}",
                        'name' => "{$p->nombre} - {$nomPersona}",
                        'tipo' => 'CARGO',
                        'raw_id' => $p->id,
                        'cargo' => $p->nombre,
                        'funcionario' => $nomPersona,
                        'color' => $regColor,
                    ];
                }

                $regNode['children'][] = $unitNode;
            }

            if (! empty($regNode['children'])) {
                $tree[] = $regNode;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $tree,
        ], Response::HTTP_OK);
    }

    /**
     * Obtiene los destinos marcados/asignados para un sujeto origen
     */
    public function destinosAsignados(Request $request): JsonResponse
    {
        $idOrigen = $request->input('id_origen');
        $tipoOrigen = strtoupper($request->input('tipo_origen', 'UNIDAD'));

        if (! $idOrigen && $tipoOrigen !== 'VENTANILLA_DIGITAL') {
            return response()->json(['success' => true, 'data' => []], Response::HTTP_OK);
        }

        $idNumeric = is_numeric($idOrigen) ? (int) $idOrigen : 1;

        $permisos = PermisoDerivacion::where('id_origen', $idNumeric)
            ->where('tipo_origen', $tipoOrigen)
            ->where('_estado', 'ACTIVO')
            ->get();

        $destinos = [];
        foreach ($permisos as $p) {
            $prefix = $p->tipo_destino === 'UNIDAD' ? 'U-' : ($p->tipo_destino === 'CARGO' || $p->tipo_destino === 'PUESTO' ? 'C-' : 'V-');
            $destinos[] = "{$prefix}{$p->id_destino}";
        }

        return response()->json([
            'success' => true,
            'data' => $destinos,
        ], Response::HTTP_OK);
    }

    /**
     * Guardar en lote todos los permisos autorizados para un sujeto origen
     */
    public function guardarLote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_origen' => 'required',
            'tipo_origen' => 'required|string',
            'destinos' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idOrigen = $request->input('id_origen');
        $tipoOrigen = strtoupper($request->input('tipo_origen'));
        $destinos = $request->input('destinos', []);

        $idNumeric = is_numeric($idOrigen) ? (int) $idOrigen : 1;

        DB::transaction(function () use ($idNumeric, $tipoOrigen, $destinos) {
            // 1. Inactivar permisos previos
            PermisoDerivacion::where('id_origen', $idNumeric)
                ->where('tipo_origen', $tipoOrigen)
                ->update(['_estado' => 'INACTIVO']);

            // 2. Insertar / Reactivar nuevos destinos
            foreach ($destinos as $dKey) {
                $tipoDestino = 'UNIDAD';
                $idDestino = 0;

                if (is_array($dKey)) {
                    $idDestino = (int) ($dKey['id_destino'] ?? $dKey['id'] ?? 0);
                    $tipoDestino = (string) ($dKey['tipo_destino'] ?? $dKey['tipo'] ?? 'UNIDAD');
                } elseif (is_string($dKey)) {
                    if (str_starts_with($dKey, 'U-')) {
                        $tipoDestino = 'UNIDAD';
                        $idDestino = (int) str_replace('U-', '', $dKey);
                    } elseif (str_starts_with($dKey, 'C-')) {
                        $tipoDestino = 'PUESTO';
                        $idDestino = (int) str_replace('C-', '', $dKey);
                    } elseif (str_starts_with($dKey, 'V-')) {
                        $tipoDestino = 'VENTANILLA';
                        $idDestino = (int) str_replace('V-', '', $dKey);
                    } elseif (is_numeric($dKey)) {
                        $idDestino = (int) $dKey;
                    }
                } elseif (is_numeric($dKey)) {
                    $idDestino = (int) $dKey;
                }

                if ($idDestino > 0) {
                    PermisoDerivacion::updateOrCreate(
                        [
                            'id_origen' => $idNumeric,
                            'tipo_origen' => $tipoOrigen,
                            'id_destino' => $idDestino,
                            'tipo_destino' => $tipoDestino,
                        ],
                        [
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'ACTUALIZAR',
                            '_usuario_modificacion' => auth()->id() ?? 1,
                            '_fecha_modificacion' => now(),
                        ]
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Permisos de derivación guardados exitosamente.',
            'total_guardados' => count($destinos),
        ], Response::HTTP_OK);
    }

    /**
     * Restablecer los permisos a las reglas por defecto
     */
    public function restablecer(Request $request): JsonResponse
    {
        $idOrigen = $request->input('id_origen');
        $tipoOrigen = strtoupper($request->input('tipo_origen', 'UNIDAD'));
        $idNumeric = is_numeric($idOrigen) ? (int) $idOrigen : 1;

        PermisoDerivacion::where('id_origen', $idNumeric)
            ->where('tipo_origen', $tipoOrigen)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Los permisos han sido restablecidos a los valores por defecto del organigrama.',
        ], Response::HTTP_OK);
    }

    /**
     * Resumen estructurado para visualización e impresión
     */
    public function resumen(Request $request): JsonResponse
    {
        $idOrigen = $request->input('id_origen');
        $tipoOrigen = strtoupper($request->input('tipo_origen', 'UNIDAD'));
        $idNumeric = is_numeric($idOrigen) ? (int) $idOrigen : 1;

        $permisos = PermisoDerivacion::where('id_origen', $idNumeric)
            ->where('tipo_origen', $tipoOrigen)
            ->where('_estado', 'ACTIVO')
            ->get();

        $unidades = [];
        $puestos = [];

        foreach ($permisos as $p) {
            if ($p->tipo_destino === 'UNIDAD') {
                $u = UnidadOrganizacional::find($p->id_destino);
                if ($u) {
                    $unidades[] = [
                        'id' => $u->id,
                        'nombre' => $u->nombre,
                        'sigla' => $u->sigla,
                    ];
                }
            } elseif ($p->tipo_destino === 'PUESTO' || $p->tipo_destino === 'CARGO') {
                $pst = Puesto::with(['unidadOrganizacional', 'asignacionesActivas.persona'])->find($p->id_destino);
                if ($pst) {
                    $persona = $pst->asignacionesActivas->first()?->persona;
                    $puestos[] = [
                        'id' => $pst->id,
                        'nombre' => $pst->nombre,
                        'unidad' => $pst->unidadOrganizacional?->nombre,
                        'funcionario' => $persona ? "{$persona->nombres} {$persona->primer_apellido}" : 'Vacante',
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'unidades_autorizadas' => $unidades,
                'puestos_autorizados' => $puestos,
                'total_destinos' => count($unidades) + count($puestos),
            ],
        ], Response::HTTP_OK);
    }
}
