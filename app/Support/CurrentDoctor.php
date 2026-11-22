<?php

namespace App\Support;

use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;

/** Médico con el que trabaja el usuario autenticado en el panel /medico. */
class CurrentDoctor
{
    public static function get(): ?Doctor
    {
        return Auth::guard('web')->user()?->activeDoctor();
    }

    public static function id(): ?int
    {
        return static::get()?->getKey();
    }

    /** Para consultas: nunca devuelve null (0 no coincide con ningún médico). */
    public static function idOrNone(): int
    {
        return static::id() ?? 0;
    }
}
