<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Registra un evento inmutable en el registro de auditoría.
     *
     * @param  string  $event  Nombre del evento (e.g. 'role_assigned', 'user_revoked', 'param_deleted')
     * @param  Model|null  $model  Modelo afectado (si aplica)
     * @param  array|null  $oldValues  Valores anteriores
     * @param  array|null  $newValues  Nuevos valores establecidos
     * @param  int|null  $userId  ID del usuario ejecutor (default: usuario autenticado)
     */
    public function log(
        string $event,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model ? $model->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
