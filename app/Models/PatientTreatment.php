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

            // Se comparan fechas (Y-m-d) en la zona de la clínica, no en la del servidor (UTC).
            $now = Carbon::now(static::timezoneForClinic($this->clinic_id));
            $dueDay = $due->toDateString();

            if ($dueDay < $now->toDateString()) {
                return 'vencido';
            }

            return $dueDay <= $now->copy()->addDays(self::SOON_DAYS)->toDateString() ? 'proxima' : 'vigente';
        });
    }

    public const DEFAULT_TIMEZONE = 'America/Bogota';

    /** @var array<int, string> */
    private static array $timezoneCache = [];

    /** Zona horaria de la clínica (clinics.timezone) o America/Bogota; cacheada por petición. */
    public static function timezoneForClinic(?int $clinicId): string
    {
        if (! $clinicId) {
            return self::DEFAULT_TIMEZONE;
        }

        if (! isset(self::$timezoneCache[$clinicId])) {
            $timezone = Clinic::query()->whereKey($clinicId)->value('timezone');
            self::$timezoneCache[$clinicId] = $timezone && in_array($timezone, timezone_identifiers_list(), true)
                ? $timezone
                : self::DEFAULT_TIMEZONE;
        }

        return self::$timezoneCache[$clinicId];
    }

    /** Hoy (Y-m-d) en la zona de la clínica. */
    public static function todayForClinic(?int $clinicId): string
    {
        return Carbon::now(static::timezoneForClinic($clinicId))->toDateString();
    }

    public const RENEWAL_LABELS = [
        'vigente' => 'Vigente',
        'proxima' => 'Renovar pronto',
        'vencido' => 'Vencido',
        'no_aplica' => 'Sin renovación',
    ];
}
