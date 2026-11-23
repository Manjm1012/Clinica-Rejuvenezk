<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Validación en el servidor de IDs elegidos en selects: el registro debe existir en la clínica actual.
 * Los selects de Filament no comprueban por sí solos que el valor enviado esté entre sus opciones,
 * así que sin esta regla una petición manipulada podía apuntar a datos de otra clínica.
 */
class ClinicRule
{
    public static function exists(string $table, ?int $clinicId = null): Exists
    {
        return Rule::exists($table, 'id')->where('clinic_id', $clinicId ?? AdminClinic::idOrNone());
    }
}
