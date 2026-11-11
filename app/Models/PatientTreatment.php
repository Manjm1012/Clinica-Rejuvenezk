<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PatientTreatment extends Model
{
    use BelongsToClinic;

    /** Días antes del vencimiento en que el tratamiento pasa a "próxima renovación". */
    public const SOON_DAYS = 30;

    /**
     * Columnas que el portal del paciente puede leer. internal_notes NUNCA está aquí:
     * las consultas del portal deben usar select(self::PORTAL_COLUMNS).
     */
    public const PORTAL_COLUMNS = [
        'patient_treatments.id', 'patient_treatments.patient_id', 'patient_treatments.service_id',
        'patient_treatments.doctor_id', 'patient_treatments.performed_at',
        'patient_treatments.notes', 'patient_treatments.next_due_at',
    ];

    protected $fillable = [
        'patient_id', 'service_id', 'doctor_id', 'performed_at', 'notes', 'internal_notes', 'next_due_at',
    ];

    protected $hidden = ['internal_notes'];

    protected function casts(): array
    {
        return [
            'performed_at' => 'date',
            'next_due_at' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** next_due_at manual o, si falta, fecha del procedimiento + intervalo del servicio. */
    protected function dueDate(): Attribute
    {
        return Attribute::get(function (): ?Carbon {
            if ($this->next_due_at) {
                return $this->next_due_at->copy();
            }

            $interval = $this->service?->renewal_interval_days;

            return $this->performed_at && $interval
                ? $this->performed_at->copy()->addDays($interval)
                : null;
        });
    }

    /** 'vigente' | 'proxima' (vence en ≤ 30 días) | 'vencido' | 'no_aplica'. */
    protected function renewalStatus(): Attribute
    {
        return Attribute::get(function (): string {
            $due = $this->due_date;

            if (! $due) {
                return 'no_aplica';
            }

            $today = Carbon::today();

            if ($due->lt($today)) {
                return 'vencido';
            }

            return $due->lte($today->copy()->addDays(self::SOON_DAYS)) ? 'proxima' : 'vigente';
        });
    }

    public const RENEWAL_LABELS = [
        'vigente' => 'Vigente',
        'proxima' => 'Renovar pronto',
        'vencido' => 'Vencido',
        'no_aplica' => 'Sin renovación',
    ];
}
