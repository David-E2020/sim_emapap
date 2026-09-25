<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\TransaccionQr;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CobroQrSimpleService
{
    /**
     * Genera una transacción de cobro QR interoperable (Simple QR - BCB / ASOBAN).
     *
     * @param array $datos [monto, glosa, id_abonado, id_caja_sesion, id_usuario, metadata]
     * @return TransaccionQr
     */
    public function generarTransaccionQr(array $datos): TransaccionQr
    {
        $monto = round((float) ($datos['monto'] ?? 0.0), 2);
        if ($monto <= 0) {
            throw new Exception('El monto del cobro QR debe ser mayor a 0.00 Bs.');
        }

        $uuid = 'QR_' . date('YmdHis') . '_' . strtoupper(Str::random(8));
        $glosa = substr((string) ($datos['glosa'] ?? 'Cobro de Servicio de Agua'), 0, 100);
        $minutosVigencia = (int) ($datos['minutos_vigencia'] ?? 10);
        $expiraAt = Carbon::now()->addMinutes($minutosVigencia);

        // Generar payload según el estándar EMVCo para Bolivia (Simple QR)
        $payloadEmvco = $this->construirPayloadEmvco($uuid, $monto, $glosa);

        // Renderizar código QR en formato SVG Base64 (compatible con 100% navegadores e impresoras térmicas)
        $qrSvg = QrCode::format('svg')
            ->size(280)
            ->margin(1)
            ->generate($payloadEmvco);

        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode((string) $qrSvg);

        return TransaccionQr::create([
            'uuid' => $uuid,
            'id_factura' => $datos['id_factura'] ?? null,
            'id_abonado' => $datos['id_abonado'] ?? null,
            'id_caja_sesion' => $datos['id_caja_sesion'] ?? null,
            'id_usuario' => $datos['id_usuario'] ?? auth()->id(),
            'monto' => $monto,
            'moneda' => 'BOB',
            'glosa' => $glosa,
            'qr_payload' => $payloadEmvco,
            'qr_imagen_base64' => $qrDataUri,
            'banco_destino' => $datos['banco_destino'] ?? 'BANCO UNION S.A.',
            'cuenta_destino' => $datos['cuenta_destino'] ?? '1000004928192',
            'estado' => TransaccionQr::ESTADO_PENDING,
            'expira_at' => $expiraAt,
            'metadata' => $datos['metadata'] ?? [],
        ]);
    }

    /**
     * Consulta el estado de una transacción QR por su UUID (polling en tiempo real).
     */
    public function consultarEstado(string $uuid): array
    {
        /** @var TransaccionQr|null $transaccion */
        $transaccion = TransaccionQr::where('uuid', $uuid)->first();
        if (!$transaccion) {
            return [
                'success' => false,
                'mensaje' => 'Transacción QR no encontrada.',
            ];
        }

        // Verificar si expiró
        if ($transaccion->estado === TransaccionQr::ESTADO_PENDING && now()->greaterThan($transaccion->expira_at)) {
            $transaccion->update(['estado' => TransaccionQr::ESTADO_EXPIRED]);
        }

        return [
            'success' => true,
            'uuid' => $transaccion->uuid,
            'estado' => $transaccion->estado,
            'monto' => (float) $transaccion->monto,
            'moneda' => $transaccion->moneda,
            'glosa' => $transaccion->glosa,
            'expira_at' => $transaccion->expira_at->toIso8601String(),
            'pagado_at' => $transaccion->pagado_at?->toIso8601String(),
            'segundos_restantes' => max(0, $transaccion->expira_at->diffInSeconds(now(), false) * -1),
            'id_factura' => $transaccion->id_factura,
            'transaccion_banco_id' => $transaccion->transaccion_banco_id,
        ];
    }

    /**
     * Registra el pago exitoso (confirmado por webhook bancario o simulación en ventanilla).
     */
    public function confirmarPago(string $uuid, ?string $transaccionBancoId = null, ?string $bancoOrigen = null): TransaccionQr
    {
        /** @var TransaccionQr|null $transaccion */
        $transaccion = TransaccionQr::where('uuid', $uuid)->firstOrFail();

        if ($transaccion->estado === TransaccionQr::ESTADO_COMPLETED) {
            return $transaccion;
        }

        if ($transaccion->estaVencido()) {
            $transaccion->update(['estado' => TransaccionQr::ESTADO_EXPIRED]);
            throw new Exception('El código QR ha expirado. Por favor genere uno nuevo.');
        }

        $transaccion->update([
            'estado' => TransaccionQr::ESTADO_COMPLETED,
            'pagado_at' => Carbon::now(),
            'transaccion_banco_id' => $transaccionBancoId ?? ('BCB-' . strtoupper(Str::random(12))),
            'metadata' => array_merge($transaccion->metadata ?? [], [
                'banco_origen' => $bancoOrigen ?? 'Banca Móvil / Simple QR',
                'confirmado_por' => auth()->user()?->name ?? 'Sistema',
            ]),
        ]);

        return $transaccion;
    }

    /**
     * Cancela una transacción QR pendiente.
     */
    public function cancelarTransaccion(string $uuid): bool
    {
        $transaccion = TransaccionQr::where('uuid', $uuid)->first();
        if ($transaccion && $transaccion->estado === TransaccionQr::ESTADO_PENDING) {
            $transaccion->update(['estado' => TransaccionQr::ESTADO_CANCELLED]);
            return true;
        }
        return false;
    }

    /**
     * Construye la cadena TLV según la especificación EMVCo de Simple QR para Bolivia.
     */
    private function construirPayloadEmvco(string $refUuid, float $monto, string $glosa): string
    {
        $montoStr = number_format($monto, 2, '.', '');

        // Sub-campos Merchant Account Information (Banco Unión / Switch BCB)
        $subGui = $this->tlv('00', 'BO.GOB.ASOBAN.SIMPLE');
        $subCuenta = $this->tlv('01', '1000004928192');
        $subRef = $this->tlv('02', $refUuid);
        $merchantAccount = $subGui . $subCuenta . $subRef;

        // Sub-campos de Additional Data
        $subBill = $this->tlv('01', $refUuid);
        $subPurpose = $this->tlv('08', substr($glosa, 0, 25));
        $additionalData = $subBill . $subPurpose;

        $rawPayload =
            $this->tlv('00', '01') .                     // Payload Format Indicator
            $this->tlv('01', '12') .                     // Dynamic QR
            $this->tlv('26', $merchantAccount) .         // Merchant Info
            $this->tlv('52', '4900') .                   // Utilities - Water Supply
            $this->tlv('53', '068') .                    // Boliviano (ISO 4217 BOB = 068)
            $this->tlv('54', $montoStr) .                // Monto exacto
            $this->tlv('58', 'BO') .                     // País
            $this->tlv('59', 'EMAPAP PATACAMAYA') .      // Beneficiario
            $this->tlv('60', 'PATACAMAYA') .             // Ciudad
            $this->tlv('62', $additionalData) .          // Datos adicionales
            '6304';                                      // Tag 63 (CRC) con longitud 04

        $crc = $this->calcularCrc16Ccitt($rawPayload);

        return $rawPayload . $crc;
    }

    /**
     * Helper Tag-Length-Value format.
     */
    private function tlv(string $tag, string $value): string
    {
        $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Cálculo de suma de verificación CRC16-CCITT (Polinomio 0x1021, Init 0xFFFF).
     */
    private function calcularCrc16Ccitt(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);
        for ($c = 0; $c < $len; $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
