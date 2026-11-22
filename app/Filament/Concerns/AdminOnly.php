<?php

namespace App\Filament\Concerns;

/**
 * Recurso o página solo para administradores de la clínica (no recepción).
 * Resource::canAccess() bloquea la navegación y todas las páginas del recurso.
 */
trait AdminOnly
{
    public static function canAccess(array $parameters = []): bool
    {
        return (bool) auth()->user()?->isClinicAdmin();
    }
}
