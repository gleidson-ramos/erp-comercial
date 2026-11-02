<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::registerAudit($model, 'created', [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();

            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $oldValues = [];

            foreach (array_keys($changes) as $field) {
                $oldValues[$field] = $model->getOriginal($field);
            }

            static::registerAudit(
                $model,
                'updated',
                $oldValues,
                $changes
            );
        });

        static::deleted(function (Model $model) {
            static::registerAudit(
                $model,
                'deleted',
                $model->getAttributes(),
                []
            );
        });
    }

    protected static function registerAudit(
        Model $model,
        string $event,
        array $oldValues,
        array $newValues
    ): void {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'remember_token',
            'api_token',
            'access_token',
            'refresh_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'client_secret',
            'private_key',
        ];

        foreach ($sensitiveFields as $field) {
            unset($oldValues[$field], $newValues[$field]);
        }

        $user = Auth::user();

        $request = app()->bound('request')
            ? request()
            : null;

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,

            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => (string) $model->getKey(),

            'event' => $event,

            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,

            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),

            'created_at' => now(),
        ]);
    }
}