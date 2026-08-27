<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DerivacionWorkflowService
{
    /**
     * Realiza una derivación de Hoja de Ruta (individual o múltiple con copias)
     */
    public function derivar(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $hojaRuta = HojaRuta::findOrFail($data['id_hoja_ruta']);
            $idDerivacionPadre = $data['id_derivacion_padre'] ?? null;
            $destinatarios = $data['destinatarios'] ?? []; // Array de [{ id_unidad, id_funcionario, id_cargo, es_copia }]
            $proveido = $data['proveido'] ?? 'PASE A SUS EFECTOS';
            $instruccion = $data['instruccion_detalle'] ?? null;
            $diasPlazo = (int)($data['dias_plazo'] ?? 2);
            $prioridad = $data['prioridad'] ?? $hojaRuta->prioridad ?? 'MEDIA';
            $fechaLimite = now()->addDays($diasPlazo)->toDateString();

            $derivacionesCreadas = [];
            $mpathPadre = null;

            if ($idDerivacionPadre) {
                $padre = Derivacion::find($idDerivacionPadre);
                if ($padre) {
                    $mpathPadre = $padre->mpath ?: (string)$padre->id;
                    // Marcar derivación anterior como PROCESADA
                    $padre->estado_derivacion = 'PROCESADO';
                    $padre->fecha_atencion = now();
                    $padre->id_usuario_atencion = auth()->id() ?? 1;
                    $padre->save();
                }
            }

            $ultimoDestinatario = null;

            foreach ($destinatarios as $index => $dest) {
                $esCopia = !empty($dest['es_copia']) || $index > 0;

                $derivacion = Derivacion::create([
                    'id_hoja_ruta' => $hojaRuta->id,
                    'id_derivacion_padre' => $idDerivacionPadre,
                    'id_documento_principal' => $data['id_documento_principal'] ?? null,
                    'id_unidad_origen' => $data['id_unidad_origen'] ?? null,
                    'id_funcionario_origen' => $data['id_funcionario_origen'] ?? null,
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
                    'participante_interino' => !empty($dest['participante_interino']),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // Establecer materialized path
                $derivacion->mpath = $mpathPadre ? "{$mpathPadre}.{$derivacion->id}" : (string)$derivacion->id;
                $derivacion->save();

                $derivacionesCreadas[] = $derivacion;

                if (!$esCopia) {
                    $ultimoDestinatario = $dest;
                }
            }

            // Actualizar estado general y "esta_con" de la Hoja de Ruta
            $hojaRuta->estado = 'EN_PROCESO';
            if ($ultimoDestinatario) {
                $nombreFuncionario = 'Funcionario';
                if (!empty($ultimoDestinatario['id_funcionario_destino'])) {
                    $persona = Persona::find($ultimoDestinatario['id_funcionario_destino']);
                    if ($persona) $nombreFuncionario = $persona->nombre_completo;
                }
                $unidad = !empty($ultimoDestinatario['id_unidad_destino']) ? UnidadOrganizacional::find($ultimoDestinatario['id_unidad_destino']) : null;

                $hojaRuta->esta_con = [
                    'id_funcionario' => $ultimoDestinatario['id_funcionario_destino'] ?? null,
                    'funcionario' => $nombreFuncionario,
                    'id_unidad' => $ultimoDestinatario['id_unidad_destino'] ?? null,
                    'unidad' => $unidad ? $unidad->nombre : 'Sin Unidad',
                    'fecha_derivacion' => now()->toDateTimeString(),
                    'estado' => 'PENDIENTE_RECEPCION',
                ];
            }
            $hojaRuta->save();

            return $derivacionesCreadas;
        });
    }

    /**
     * Recepción de una derivación en bandeja
     */
    public function recibir(int $idDerivacion, int $idPersona): Derivacion
    {
        return DB::transaction(function () use ($idDerivacion, $idPersona) {
            $derivacion = Derivacion::findOrFail($idDerivacion);
            $derivacion->estado_derivacion = 'RECIBIDO';
            $derivacion->fecha_recepcion = now();
            $derivacion->_usuario_modificacion = auth()->id() ?? 1;
            $derivacion->_fecha_modificacion = now();
            $derivacion->save();

            // Actualizar esta_con en la hoja de ruta
            $hojaRuta = $derivacion->hojaRuta;
            if ($hojaRuta && is_array($hojaRuta->esta_con)) {
                $estaCon = $hojaRuta->esta_con;
                $estaCon['estado'] = 'RECIBIDO';
                $estaCon['fecha_recepcion'] = now()->toDateTimeString();
                $hojaRuta->esta_con = $estaCon;
                $hojaRuta->save();
            }

            return $derivacion;
        });
    }

    /**
     * Devolución de una derivación con observaciones
     */
    public function devolver(int $idDerivacion, int $idPersona, string $motivo): Derivacion
    {
        return DB::transaction(function () use ($idDerivacion, $idPersona, $motivo) {
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
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            return $retorno;
        });
    }

    /**
     * Calcula el estado del semáforo para una derivación
     */
    public function calcularSemaforo(Derivacion $derivacion): array
    {
        if (in_array($derivacion->estado_derivacion, ['PROCESADO', 'ARCHIVADO'])) {
            return ['color' => 'success', 'texto' => 'Atendido', 'dias_restantes' => 0];
        }

        $fechaInicio = Carbon::parse($derivacion->fecha_derivacion);
        $diasTranscurridos = (int)$fechaInicio->diffInDays(now());
        $diasPlazo = $derivacion->dias_plazo ?: 2;
        $diasRestantes = $diasPlazo - $diasTranscurridos;

        if ($diasRestantes < 0) {
            return ['color' => 'error', 'texto' => "Vencido hace " . abs($diasRestantes) . " día(s)", 'dias_restantes' => $diasRestantes];
        } elseif ($diasRestantes <= 1) {
            return ['color' => 'warning', 'texto' => "Por vencer ({$diasRestantes} día)", 'dias_restantes' => $diasRestantes];
        }

        return ['color' => 'success', 'texto' => "En plazo ({$diasRestantes} días)", 'dias_restantes' => $diasRestantes];
    }
}
