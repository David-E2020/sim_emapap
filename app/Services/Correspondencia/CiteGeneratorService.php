<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Correlativo;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CiteGeneratorService
{
    /**
     * Genera un CITE atómico y secuencial para Hojas de Ruta: HR-EMAPA-0001/2026
     */
    public function generarCiteHojaRuta(?int $idRegional = null, ?int $gestion = null): string
    {
        $gestion = $gestion ?: (int) date('Y');
        $siglaRegional = 'NAL';

        if ($idRegional) {
            $reg = Regional::find($idRegional);
            if ($reg && ! empty($reg->sigla)) {
                $siglaRegional = strtoupper(trim($reg->sigla));
            }
        }

        return DB::transaction(function () use ($gestion, $siglaRegional) {
            $correlativo = Correlativo::where('gestion', $gestion)
                ->where('tipo_correlativo', 'HOJA_RUTA')
                ->where('sigla_regional', $siglaRegional)
                ->lockForUpdate()
                ->first();

            if (! $correlativo) {
                $correlativo = Correlativo::create([
                    'gestion' => $gestion,
                    'tipo_correlativo' => 'HOJA_RUTA',
                    'sigla_regional' => $siglaRegional,
                    'correlativo_actual' => 0,
                    'formato_cite' => 'HR-EMAPA-{REG}-{NRO}/{GESTION}',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);
            }

            $nuevoNro = $correlativo->correlativo_actual + 1;
            $correlativo->correlativo_actual = $nuevoNro;
            $correlativo->_usuario_modificacion = auth()->id() ?? 1;
            $correlativo->_fecha_modificacion = now();
            $correlativo->save();

            $nroPad = str_pad((string) $nuevoNro, 4, '0', STR_PAD_LEFT);

            return "HR-EMAPA-{$siglaRegional}-{$nroPad}/{$gestion}";
        });
    }

    /**
     * Genera un CITE atómico para Documentos Oficiales: EMAPA/GG/MEM/001/2026
     */
    public function generarCiteDocumento(int $idUnidad, string $siglaPlantilla, ?int $gestion = null): string
    {
        $gestion = $gestion ?: (int) date('Y');
        $siglaPlantilla = strtoupper(trim($siglaPlantilla));

        $unidad = UnidadOrganizacional::find($idUnidad);
        $siglaUnidad = $unidad && ! empty($unidad->sigla) ? strtoupper(trim($unidad->sigla)) : 'ORG';

        return DB::transaction(function () use ($gestion, $idUnidad, $siglaPlantilla, $siglaUnidad) {
            $correlativo = Correlativo::where('gestion', $gestion)
                ->where('tipo_correlativo', 'DOCUMENTO')
                ->where('id_unidad_organizacional', $idUnidad)
                ->where('sigla_plantilla', $siglaPlantilla)
                ->lockForUpdate()
                ->first();

            if (! $correlativo) {
                $correlativo = Correlativo::create([
                    'gestion' => $gestion,
                    'tipo_correlativo' => 'DOCUMENTO',
                    'id_unidad_organizacional' => $idUnidad,
                    'sigla_plantilla' => $siglaPlantilla,
                    'correlativo_actual' => 0,
                    'formato_cite' => "EMAPA/{$siglaUnidad}/{$siglaPlantilla}/{NRO}/{$gestion}",
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);
            }

            $nuevoNro = $correlativo->correlativo_actual + 1;
            $correlativo->correlativo_actual = $nuevoNro;
            $correlativo->_usuario_modificacion = auth()->id() ?? 1;
            $correlativo->_fecha_modificacion = now();
            $correlativo->save();

            $nroPad = str_pad((string) $nuevoNro, 3, '0', STR_PAD_LEFT);

            return "EMAPA/{$siglaUnidad}/{$siglaPlantilla}/{$nroPad}/{$gestion}";
        });
    }

    /**
     * Genera un token alfanumérico seguro para validación QR
     */
    public function generarCodigoVerificacion(): string
    {
        do {
            $codigo = strtoupper(Str::random(8));
        } while (DB::table('correspondencia.documentos')->where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }
}
