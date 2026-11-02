<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditService
{
    /**
     * Atualiza registros em massa e registra a auditoria.
     */
    public function bulkUpdate(
        string $modelClass,
        array $ids,
        array $data,
        string $description = 'Atualização em massa'
    ): int {
        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException(
                'A classe informada não é um Model Eloquent.'
            );
        }

        if (empty($ids) || empty($data)) {
            return 0;
        }

        return DB::transaction(function () use (
            $modelClass,
            $ids,
            $data,
            $description
        ) {
            $model = new $modelClass();

            $query = $modelClass::query()
                ->whereKey($ids)
                ->lockForUpdate();

            $records = $query->get();

            $count = 0;

            foreach ($records as $record) {
                $oldValues = [];

                foreach ($data as $field => $value) {
                    $oldValues[$field] = $record->getAttribute($field);
                }

                $record->fill($data);

                if (! $record->isDirty()) {
                    continue;
                }

                $changes = $record->getDirty();

                $record->save();

                $newValues = [];

                foreach (array_keys($changes) as $field) {
                    $newValues[$field] = $record->getAttribute($field);
                }

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()?->name,
                    'user_email' => Auth::user()?->email,
                    'auditable_type' => $record->getMorphClass(),
                    'auditable_id' => (string) $record->getKey(),
                    'event' => 'updated',
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);

                $count++;
            }

            return $count;
        });
    }
}