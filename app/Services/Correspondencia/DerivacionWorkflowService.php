<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\AgrupacionHojaRuta;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\UnidadOrganizacional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class DerivacionWorkflowService
{
    /**
     * Realiza una derivación de Hoja de Ruta (individual o múltiple con copias CC) - LONDRA REVERSE ENGINEERED
     */
    public function derivar(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $hojaRuta = HojaRuta::findOrFail($data['id_hoja_ruta']);
            $idDerivacionPadre = $data['id_derivacion_padre'] ?? null;
            $destinatarios = $data['destinatarios'] ?? []; // Array de [{ id_unidad_destino, id_funcionario_destino, id_cargo_destino, es_copia }]
            $proveido = $data['proveido'] ?? 'PASE A SUS EFECTOS';
            $instruccion = $data['instruccion_detalle'] ?? null;
            $diasPlazo = (int) ($data['dias_plazo'] ?? 2);
            $prioridad = $data['prioridad'] ?? $hojaRuta->prioridad ?? 'MEDIA';
            $fechaLimite = now()->addDays($diasPlazo)->toDateString();

            if (empty($destinatarios)) {
                throw new PreconditionFailedHttpException('Debe especificar al menos un destinatario.');
            }

            $derivacionesCreadas = [];
            $mpathPadre = null;

            if ($idDerivacionPadre) {
                $padre = Derivacion::find($idDerivacionPadre);
                if ($padre) {
                    $mpathPadre = $padre->mpath ?: (string) $padre->id;
                    // Marcar derivación anterior como PROCESADA
                    $padre->estado_derivacion = 'PROCESADO';
                    $padre->fecha_atencion = now();
                    $padre->id_usuario_atencion = auth()->id() ?? 1;
                    $padre->_usuario_modificacion = auth()->id() ?? 1;
                    $padre->_fecha_modificacion = now();
                    $padre->save();
                }
            }

            $destinatarioPrincipal = null;

            foreach ($destinatarios as $index => $dest) {
                $esCopia = ! empty($dest['es_copia']);

                $derivacion = Derivacion::create([
                    'id_hoja_ruta' => $hojaRuta->id,
                    'id_derivacion_padre' => $idDerivacionPadre,
                    'id_documento_principal' => $data['id_documento_principal'] ?? null,
                    'id_unidad_origen' => $data['id_unidad_origen'] ?? null,
                    'id_funcionario_origen' => $data['id_funcionario_origen'] ?? (auth()->user()?->id_persona ?: 1),
                    'id_cargo_origen' => $data['id_cargo_origen'] ?? null,
                    'id_unidad_destino' => $dest['id_unidad_destino'] ?? null,
                    'id_funcionario_destino' => $dest['id_funcionario_destino'] ?? null,
                    'id_cargo_destino' => $dest['id_cargo_destino'] ?? null,
                    'proveido' => $proveido,
                    'instruccion_detalle' => $instruccion,
                    'prioridad' => $prioridad,
                    'dias_plazo' => $diasPlazo,
                    'fecha_limite' => $fechaLimite,
                    'fecha_derivacion' => now(),
                    'estado_derivacion' => 'PENDIENTE_RECEPCION',
                    'es_copia' => $esCopia,
                    'participante_interino' => ! empty($dest['participante_interino']),
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // Establecer materialized path (mpath)
                $derivacion->mpath = $mpathPadre ? "{$mpathPadre}.{$derivacion->id}" : (string) $derivacion->id;
                $derivacion->save();

                $derivacionesCreadas[] = $derivacion;

                if (! $esCopia && ! $destinatarioPrincipal) {
                    $destinatarioPrincipal = $dest;
                }
            }

            // Si todos eran copia, usar el primero como principal
            if (! $destinatarioPrincipal && ! empty($destinatarios)) {
                $destinatarioPrincipal = $destinatarios[0];
            }

            // Actualizar estado general y "esta_con" de la Hoja de Ruta
            $hojaRuta->estado = 'EN_PROCESO';
            if ($destinatarioPrincipal) {
                $nombreFuncionario = 'Funcionario / Unidad';
                if (! empty($destinatarioPrincipal['id_funcionario_destino'])) {
                    $persona = Persona::find($destinatarioPrincipal['id_funcionario_destino']);
                    if ($persona) {
                        $nombreFuncionario = $persona->nombre_completo;
                    }
                }
                $unidad = ! empty($destinatarioPrincipal['id_unidad_destino']) ? UnidadOrganizacional::find($destinatarioPrincipal['id_unidad_destino']) : null;

                $hojaRuta->esta_con = [
                    'id_funcionario' => $destinatarioPrincipal['id_funcionario_destino'] ?? null,
                    'funcionario' => $nombreFuncionario,
                    'id_unidad' => $destinatarioPrincipal['id_unidad_destino'] ?? null,
                    'unidad' => $unidad ? $unidad->nombre : 'Sin Unidad',
                    'fecha_derivacion' => now()->toDateTimeString(),
                    'estado' => 'PENDIENTE_RECEPCION',
                ];
            }
            $hojaRuta->_usuario_modificacion = auth()->id() ?? 1;
            $hojaRuta->_fecha_modificacion = now();
            $hojaRuta->save();

            return $derivacionesCreadas;
        });
    }

    /**
     * Recepción de una derivación en bandeja
     */
    public function recibir(int $idDerivacion, int $idPersona): Derivacion
    {
        return DB::transaction(function () use ($idDerivacion) {
            $derivacion = Derivacion::findOrFail($idDerivacion);
            $derivacion->estado_derivacion = 'RECIBIDO';
            $derivacion->fecha_recepcion = now();
            $derivacion->_usuario_modificacion = auth()->id() ?? 1;
            $derivacion->_fecha_modificacion = now();
            $derivacion->save();

            // Actualizar esta_con en la hoja de ruta
            $hojaRuta = $derivacion->hojaRuta;
            if ($hojaRuta) {
                $estaCon = is_array($hojaRuta->esta_con) ? $hojaRuta->esta_con : [];
                $estaCon['estado'] = 'RECIBIDO';
                $estaCon['fecha_recepcion'] = now()->toDateTimeString();
                $hojaRuta->esta_con = $estaCon;
                $hojaRuta->_usuario_modificacion = auth()->id() ?? 1;
                $hojaRuta->_fecha_modificacion = now();
                $hojaRuta->save();
            }

            return $derivacion;
        });
    }

    public function recepcionar(int $idDerivacion, int $idPersona): Derivacion
    {
        return $this->recibir($idDerivacion, $idPersona);
    }

    /**
     * Devolución de una derivación con observaciones (LONDRA: Retorno al remitente)
     */
    public function devolver(int $idDerivacion, int $idPersona, string $motivo): Derivacion
    {
        return DB::transaction(function () use ($idDerivacion, $motivo) {
            $derivacion = Derivacion::findOrFail($idDerivacion);
            $derivacion->estado_derivacion = 'DEVUELTO_OBSERVADO';
            $derivacion->observacion_devolucion = $motivo;
            $derivacion->fecha_atencion = now();
            $derivacion->id_usuario_atencion = auth()->id() ?? 1;
            $derivacion->_usuario_modificacion = auth()->id() ?? 1;
            $derivacion->_fecha_modificacion = now();
            $derivacion->save();

            // Crear derivación de retorno al remitente original
            $retorno = Derivacion::create([
                'id_hoja_ruta' => $derivacion->id_hoja_ruta,
                'id_derivacion_padre' => $derivacion->id,
                'mpath' => "{$derivacion->mpath}.retorno",
                'id_documento_principal' => $derivacion->id_documento_principal,
                'id_unidad_origen' => $derivacion->id_unidad_destino,
                'id_funcionario_origen' => $derivacion->id_funcionario_destino,
                'id_cargo_origen' => $derivacion->id_cargo_destino,
                'id_unidad_destino' => $derivacion->id_unidad_origen,
                'id_funcionario_destino' => $derivacion->id_funcionario_origen,
                'id_cargo_destino' => $derivacion->id_cargo_origen,
                'proveido' => 'DEVUELTO CON OBSERVACIONES',
                'instruccion_detalle' => $motivo,
                'prioridad' => 'ALTA',
                'dias_plazo' => 1,
                'fecha_derivacion' => now(),
                'estado_derivacion' => 'PENDIENTE_RECEPCION',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            // Actualizar esta_con en la hoja de ruta hacia el remitente que debe subsanar
            $hojaRuta = $derivacion->hojaRuta;
            if ($hojaRuta) {
                $remitente = Persona::find($derivacion->id_funcionario_origen);
                $unidad = UnidadOrganizacional::find($derivacion->id_unidad_origen);
                $hojaRuta->esta_con = [
                    'id_funcionario' => $derivacion->id_funcionario_origen,
                    'funcionario' => $remitente ? $remitente->nombre_completo : 'Funcionario',
                    'id_unidad' => $derivacion->id_unidad_origen,
                    'unidad' => $unidad ? $unidad->nombre : 'Sin Unidad',
                    'fecha_derivacion' => now()->toDateTimeString(),
                    'estado' => 'PENDIENTE_RECEPCION',
                    'observacion' => "Devuelto: {$motivo}",
                ];
                $hojaRuta->save();
            }

            return $retorno;
        });
    }

    /**
     * Anular / Deshacer una derivación en tránsito (LONDRA: Si no fue recibida aún)
     */
    public function anularDerivacion(int $idDerivacion, int $idPersona): bool
    {
        return DB::transaction(function () use ($idDerivacion, $idPersona) {
            $derivacion = Derivacion::findOrFail($idDerivacion);

            if ($derivacion->estado_derivacion !== 'PENDIENTE_RECEPCION') {
                throw new PreconditionFailedHttpException('No se puede anular la derivación porque el destinatario ya la ha recepcionado.');
            }

            if ($derivacion->id_funcionario_origen && (int) $derivacion->id_funcionario_origen !== $idPersona && auth()->id() !== 1) {
                throw new PreconditionFailedHttpException('Solo el funcionario remitente puede anular esta derivación.');
            }

            $idPadre = $derivacion->id_derivacion_padre;
            $hojaRuta = $derivacion->hojaRuta;

            // Marcar derivación como inactiva / anulada
            $derivacion->_estado = 'INACTIVO';
            $derivacion->estado_derivacion = 'ANULADO';
            $derivacion->_usuario_modificacion = auth()->id() ?? 1;
            $derivacion->_fecha_modificacion = now();
            $derivacion->save();

            // Si tenía derivación padre, reactivarla
            if ($idPadre) {
                $padre = Derivacion::find($idPadre);
                if ($padre) {
                    $padre->estado_derivacion = 'RECIBIDO';
                    $padre->fecha_atencion = null;
                    $padre->id_usuario_atencion = null;
                    $padre->_usuario_modificacion = auth()->id() ?? 1;
                    $padre->_fecha_modificacion = now();
                    $padre->save();

                    // Restablecer esta_con hacia el remitente
                    if ($hojaRuta) {
                        $remitente = Persona::find($padre->id_funcionario_destino);
                        $unidad = UnidadOrganizacional::find($padre->id_unidad_destino);
                        $hojaRuta->esta_con = [
                            'id_funcionario' => $padre->id_funcionario_destino,
                            'funcionario' => $remitente ? $remitente->nombre_completo : 'Funcionario',
                            'id_unidad' => $padre->id_unidad_destino,
                            'unidad' => $unidad ? $unidad->nombre : 'Sin Unidad',
                            'fecha_derivacion' => $padre->fecha_derivacion,
                            'fecha_recepcion' => $padre->fecha_recepcion,
                            'estado' => 'RECIBIDO',
                        ];
                        $hojaRuta->save();
                    }
                }
            } else {
                // Era la primera derivación de la HR
                if ($hojaRuta) {
                    $hojaRuta->estado = 'CREADO';
                    $hojaRuta->esta_con = [
                        'id_funcionario' => $hojaRuta->id_persona_origen,
                        'id_unidad' => $hojaRuta->id_unidad_origen,
                        'estado' => 'RECIBIDO',
                    ];
                    $hojaRuta->save();
                }
            }

            return true;
        });
    }

    /**
     * Agrupar dos Hojas de Ruta (LONDRA: Anexar HR Hija a HR Madre)
     */
    public function agrupar(int $idHojaRutaPrincipal, int $idHojaRutaAnexada, int $idPersona, ?string $motivo = null): AgrupacionHojaRuta
    {
        return DB::transaction(function () use ($idHojaRutaPrincipal, $idHojaRutaAnexada, $motivo) {
            $madre = HojaRuta::findOrFail($idHojaRutaPrincipal);
            $hija = HojaRuta::findOrFail($idHojaRutaAnexada);

            if ($madre->id === $hija->id) {
                throw new PreconditionFailedHttpException('No se puede agrupar una hoja de ruta consigo misma.');
            }

            if ($madre->estado === 'CERRADO' || $hija->estado === 'CERRADO') {
                throw new PreconditionFailedHttpException('No se pueden agrupar hojas de ruta cerradas o archivadas.');
            }

            $agrupacion = AgrupacionHojaRuta::create([
                'id_hoja_ruta_principal' => $madre->id,
                'id_hoja_ruta_anexada' => $hija->id,
                'motivo_agrupacion' => $motivo ?: 'Agrupación de hoja de ruta.',
                'fecha_agrupacion' => now(),
                'id_usuario_agrupacion' => auth()->id() ?? 1,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            $hija->estado = 'AGRUPADO';
            $hija->id_hoja_ruta_padre = $madre->id;
            $hija->_usuario_modificacion = auth()->id() ?? 1;
            $hija->_fecha_modificacion = now();
            $hija->save();

            return $agrupacion;
        });
    }

    /**
     * Desagrupar una Hoja de Ruta previamente agrupada
     */
    public function desagrupar(int $idAgrupacion, int $idPersona): bool
    {
        return DB::transaction(function () use ($idAgrupacion) {
            $agrupacion = AgrupacionHojaRuta::findOrFail($idAgrupacion);
            $hija = HojaRuta::find($agrupacion->id_hoja_ruta_anexada);

            $agrupacion->_estado = 'INACTIVO';
            $agrupacion->fecha_desagrupacion = now();
            $agrupacion->_usuario_modificacion = auth()->id() ?? 1;
            $agrupacion->_fecha_modificacion = now();
            $agrupacion->save();

            if ($hija) {
                $hija->estado = 'EN_PROCESO';
                $hija->id_hoja_ruta_padre = null;
                $hija->_usuario_modificacion = auth()->id() ?? 1;
                $hija->_fecha_modificacion = now();
                $hija->save();
            }

            return true;
        });
    }

    /**
     * Concluir y Archivar Hoja de Ruta (LONDRA: CERRADO)
     */
    public function cerrarHojaRuta(int $idHojaRuta, int $idPersona, string $motivo): HojaRuta
    {
        return DB::transaction(function () use ($idHojaRuta, $idPersona, $motivo) {
            $hojaRuta = HojaRuta::findOrFail($idHojaRuta);
            $hojaRuta->estado = 'CERRADO';
            $hojaRuta->fecha_cierre = now();
            $hojaRuta->id_usuario_cierre = auth()->id() ?? 1;
            $hojaRuta->id_cargo_cierre = $idPersona;
            $hojaRuta->motivo_cierre = $motivo;
            $hojaRuta->_usuario_modificacion = auth()->id() ?? 1;
            $hojaRuta->_fecha_modificacion = now();
            $hojaRuta->save();

            // Marcar última derivación activa como PROCESADA
            $ultimaDer = $hojaRuta->derivaciones()->where('_estado', 'ACTIVO')->latest('id')->first();
            if ($ultimaDer && $ultimaDer->estado_derivacion === 'RECIBIDO') {
                $ultimaDer->estado_derivacion = 'PROCESADO';
                $ultimaDer->fecha_atencion = now();
                $ultimaDer->id_usuario_atencion = auth()->id() ?? 1;
                $ultimaDer->save();
            }

            return $hojaRuta;
        });
    }

    /**
     * Reabrir una Hoja de Ruta archivada / concluida
     */
    public function reabrirHojaRuta(int $idHojaRuta, int $idPersona, string $motivo): HojaRuta
    {
        return DB::transaction(function () use ($idHojaRuta, $idPersona, $motivo) {
            $hojaRuta = HojaRuta::findOrFail($idHojaRuta);
            $hojaRuta->estado = 'EN_PROCESO';
            $hojaRuta->fecha_reapertura = now();
            $hojaRuta->id_usuario_reapertura = auth()->id() ?? 1;
            $hojaRuta->id_cargo_reapertura = $idPersona;
            $hojaRuta->motivo_reapertura = $motivo;
            $hojaRuta->_usuario_modificacion = auth()->id() ?? 1;
            $hojaRuta->_fecha_modificacion = now();
            $hojaRuta->save();

            return $hojaRuta;
        });
    }

    /**
     * Calcula las acciones permitidas para el usuario en una Hoja de Ruta (LONDRA AccionPermitida)
     */
    public function calcularAccionesPermitidas(HojaRuta $hr, int $idPersona, ?Derivacion $miDerivacion): array
    {
        $esCerrada = ($hr->estado === 'CERRADO');
        $esAnulada = ($hr->estado === 'ANULADO');
        $esAgrupada = ($hr->estado === 'AGRUPADO');

        $puedeRecibir = false;
        $puedeDerivar = false;
        $puedeDevolver = false;
        $puedeCerrar = false;
        $puedeReabrir = false;
        $puedeAnularDerivacion = false;
        $puedeAgrupar = false;
        $puedeDesagrupar = false;

        if (! $esCerrada && ! $esAnulada) {
            if ($miDerivacion) {
                if ($miDerivacion->estado_derivacion === 'PENDIENTE_RECEPCION') {
                    $puedeRecibir = true;
                    $puedeDevolver = ! empty($miDerivacion->id_derivacion_padre);
                } elseif ($miDerivacion->estado_derivacion === 'RECIBIDO') {
                    $puedeDerivar = true;
                    $puedeDevolver = ! empty($miDerivacion->id_derivacion_padre);
                    $puedeCerrar = true;
                    $puedeAgrupar = true;
                }
            }

            // Puede anular si el usuario derivó y aún no fue recibido
            $derivacionEnTransito = $hr->derivaciones
                ->where('id_funcionario_origen', $idPersona)
                ->where('estado_derivacion', 'PENDIENTE_RECEPCION')
                ->where('_estado', 'ACTIVO')
                ->first();

            if ($derivacionEnTransito) {
                $puedeAnularDerivacion = true;
            }
        }

        if ($esCerrada) {
            $puedeReabrir = true;
        }

        if ($esAgrupada) {
            $puedeDesagrupar = true;
        }

        return [
            'puede_recibir' => $puedeRecibir,
            'puede_derivar' => $puedeDerivar,
            'puede_devolver' => $puedeDevolver,
            'puede_anular_derivacion' => $puedeAnularDerivacion,
            'puede_cerrar' => $puedeCerrar,
            'puede_reabrir' => $puedeReabrir,
            'puede_agrupar' => $puedeAgrupar,
            'puede_desagrupar' => $puedeDesagrupar,
            'puede_imprimir' => true,
        ];
    }

    /**
     * Calcula el estado del semáforo para una derivación
     */
    public function calcularSemaforo(Derivacion $derivacion): array
    {
        if (in_array($derivacion->estado_derivacion, ['PROCESADO', 'ARCHIVADO', 'ANULADO'])) {
            return ['color' => 'success', 'texto' => 'Atendido', 'dias_restantes' => 0];
        }

        $fechaInicio = Carbon::parse($derivacion->fecha_derivacion);
        $diasTranscurridos = (int) $fechaInicio->diffInDays(now());
        $diasPlazo = $derivacion->dias_plazo ?: 2;
        $diasRestantes = $diasPlazo - $diasTranscurridos;

        if ($diasRestantes < 0) {
            return ['color' => 'error', 'texto' => 'Vencido hace '.abs($diasRestantes).' día(s)', 'dias_restantes' => $diasRestantes];
        } elseif ($diasRestantes <= 1) {
            return ['color' => 'warning', 'texto' => "Por vencer ({$diasRestantes} día)", 'dias_restantes' => $diasRestantes];
        }

        return ['color' => 'success', 'texto' => "En plazo ({$diasRestantes} días)", 'dias_restantes' => $diasRestantes];
    }
}
