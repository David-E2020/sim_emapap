<?php

declare(strict_types=1);

namespace App\Services\Administracion;

use App\Models\Parametrica;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ParametricaService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Obtiene las tablas origen con el conteo de sus campos activos en una sola consulta (Anti N+1).
     */
    public function getOriginTables(): Collection
    {
        return Parametrica::query()
            ->select(['id', 'param_nombre', 'param_codigo', 'param_descripcion', 'param_tabla', 'param_valor', 'param_estado'])
            ->where('param_codigo', 'ORIGEN')
            ->where('param_valor', 0)
            ->where('param_estado', 'A')
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('parametricas as sub')
                    ->whereColumn('sub.param_tabla', 'parametricas.param_tabla')
                    ->where('sub.param_estado', 'A')
                    ->where('sub.param_valor', '>', 0);
            }, 'param_valor_contador')
            ->orderBy('param_tabla', 'ASC')
            ->get();
    }

    /**
     * Obtiene los campos específicos de una tabla paramétrica con conteo optimizado.
     */
    public function getFieldsByTable(string $paramTabla): Collection
    {
        return Parametrica::query()
            ->select(['id', 'param_nombre', 'param_codigo', 'param_descripcion', 'param_tabla', 'param_valor', 'param_estado'])
            ->where('param_estado', 'A')
            ->where('param_tabla', $paramTabla)
            ->where('param_valor', '>', 0)
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('parametricas as sub')
                    ->whereColumn('sub.param_tabla', 'parametricas.param_tabla')
                    ->where('sub.param_estado', 'A');
            }, 'param_valor_contador')
            ->orderBy('id', 'ASC')
            ->get();
    }

    /**
     * Registra o actualiza una tabla o campo paramétrico de forma transaccional.
     */
    public function save(array $data, ?int $userId = null): Parametrica
    {
        return DB::transaction(function () use ($data, $userId) {
            $userId = $userId ?? auth()->id();
            $isNew = empty($data['id']);

            if (!$isNew) {
                $parametrica = Parametrica::findOrFail((int)$data['id']);
                $oldValues = $parametrica->only(['param_nombre', 'param_descripcion', 'param_tabla', 'param_valor']);
            } else {
                $parametrica = new Parametrica();
                $oldValues = null;
            }

            $parametrica->param_tabla = strtoupper(trim($data['param_tabla'] ?? ''));
            $parametrica->param_nombre = strtoupper(trim($data['param_nombre'] ?? ''));
            $parametrica->param_descripcion = isset($data['param_descripcion']) ? strtoupper(trim($data['param_descripcion'])) : null;
            $parametrica->param_codigo = $data['param_codigo'] ?? 'ORIGEN';
            $parametrica->param_valor = (int)($data['param_valor'] ?? 0);
            $parametrica->param_estado = 'A';

            if ($isNew) {
                $parametrica->param_usr_registrado = $userId;
            } else {
                $parametrica->param_usr_modificado = $userId;
            }

            $parametrica->save();

            // Auditoría inmutable
            $this->auditService->log(
                event: $isNew ? 'parametrica_created' : 'parametrica_updated',
                model: $parametrica,
                oldValues: $oldValues,
                newValues: $parametrica->only(['param_nombre', 'param_descripcion', 'param_tabla', 'param_valor']),
                userId: $userId
            );

            return $parametrica;
        });
    }

    /**
     * Elimina lógicamente una paramétrica validando dependencias hijo.
     */
    public function delete(int $id, ?int $userId = null): Parametrica
    {
        return DB::transaction(function () use ($id, $userId) {
            $userId = $userId ?? auth()->id();
            $parametrica = Parametrica::findOrFail($id);

            // Si es una tabla origen (valor 0), validar que no tenga campos hijos activos
            if ((int)$parametrica->param_valor === 0) {
                $hasActiveChildren = Parametrica::where('param_tabla', $parametrica->param_tabla)
                    ->where('param_valor', '<>', 0)
                    ->where('param_estado', 'A')
                    ->exists();

                if ($hasActiveChildren) {
                    throw new RuntimeException("No se puede eliminar la tabla '{$parametrica->param_tabla}' porque aún contiene subcampos activos asociados.");
                }
            }

            $oldValues = $parametrica->only(['param_estado', 'deleted_at']);

            $parametrica->param_estado = 'B';
            $parametrica->param_usr_eliminado = $userId;
            $parametrica->deleted_at = now();
            $parametrica->save();

            // Auditoría inmutable
            $this->auditService->log(
                event: 'parametrica_deleted',
                model: $parametrica,
                oldValues: $oldValues,
                newValues: ['param_estado' => 'B', 'deleted_at' => (string)now()],
                userId: $userId
            );

            return $parametrica;
        });
    }
}
