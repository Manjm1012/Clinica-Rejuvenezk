<?php

namespace App\Support;

use App\Models\PatientTreatment;
use Illuminate\Support\Collection;

/**
 * Cálculo de renovaciones. Solo cuenta el último tratamiento de cada paciente por servicio:
 * si alguien repitió la toxina, la aplicación anterior ya no está "vencida".
 */
class Renewals
{
    /** Clave paciente+servicio; los tratamientos sin servicio se tratan por separado. */
    public static function key(PatientTreatment $treatment): string
    {
        return $treatment->service_id
            ? $treatment->patient_id.'-'.$treatment->service_id
            : 'sin-servicio-'.$treatment->id;
    }

    /**
     * Ids de los tratamientos que son el más reciente de su paciente+servicio.
     * $treatments debe venir ordenada por performed_at desc, id desc.
     */
    public static function currentIds(Collection $treatments): array
    {
        return $treatments->unique(fn (PatientTreatment $t) => static::key($t))->pluck('id')->all();
    }

    /** Tratamientos vigentes (último por paciente+servicio) de una clínica, con su servicio. */
    public static function currentTreatments(int $clinicId): Collection
    {
        return PatientTreatment::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->select(['id', 'clinic_id', 'patient_id', 'service_id', 'performed_at', 'next_due_at'])
            ->with(['service' => fn ($query) => $query->withoutGlobalScope('clinic')->select(['id', 'renewal_interval_days'])])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->get()
            ->unique(fn (PatientTreatment $t) => static::key($t))
            ->values();
    }

    /**
     * Tratamientos por renovar (por defecto: próximos ≤ 30 días y vencidos), ordenados por fecha.
     * Nunca incluye 'no_aplica'.
     */
    public static function due(int $clinicId, array $statuses = ['proxima', 'vencido']): Collection
    {
        $statuses = array_values(array_diff($statuses, ['no_aplica']));

        return static::currentTreatments($clinicId)
            ->filter(fn (PatientTreatment $t) => in_array($t->renewal_status, $statuses, true))
            ->sortBy(fn (PatientTreatment $t) => $t->due_date->toDateString())
            ->values();
    }

    /** Enlace de WhatsApp a un paciente (agrega el indicativo 57 a celulares colombianos de 10 dígitos). */
    public static function patientWhatsappUrl(?string $phone, string $text): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            $digits = '57'.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }
}
