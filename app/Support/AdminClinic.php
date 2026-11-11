<?php

namespace App\Support;

use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;

/**
 * Clínica con la que trabaja el usuario del panel Filament.
 *
 * En /admin no corre ResolveClinic (y en las peticiones de Livewire la clínica se resuelve
 * por host), así que los recursos nuevos NO dependen del global scope: filtran y asignan
 * clinic_id explícitamente con este valor.
 */
class AdminClinic
{
    public static function id(): ?int
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return null;
        }

        if ($user->clinic_id) {
            return (int) $user->clinic_id;
        }

        // Super admin sin clínica: la clínica activa (misma regla que ResolveClinic por defecto).
        if ($user->is_super_admin) {
            $id = Clinic::query()->where('is_active', true)->orderBy('id')->value('id');

            return $id ? (int) $id : null;
        }

        return null;
    }

    /** Para consultas: nunca devuelve null (0 no coincide con ninguna clínica). */
    public static function idOrNone(): int
    {
        return static::id() ?? 0;
    }
}
