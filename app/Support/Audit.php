<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit
{
    /** @param  array<int, string>  $fields  nombres de campos modificados (sin valores) */
    public static function log(string $action, Model $model, array $fields = []): void
    {
        [$type, $id, $name] = static::actor();
        $request = app()->runningInConsole() && ! app()->runningUnitTests() ? null : request();

        AuditLog::query()->create([
            'clinic_id' => $model->getAttribute('clinic_id'),
            'actor_type' => $type,
            'actor_id' => $id,
            'actor_name' => $name,
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'changes' => $fields ? array_values($fields) : null,
            'ip' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }

    /** @return array{0: string, 1: ?int, 2: ?string} */
    private static function actor(): array
    {
        if ($user = Auth::guard('web')->user()) {
            return ['user', $user->getKey(), $user->name];
        }

        if ($patient = Auth::guard('patient')->user()) {
            return ['patient', $patient->getKey(), $patient->name];
        }

        return ['system', null, null];
    }
}
