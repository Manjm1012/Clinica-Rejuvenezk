<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud del portal: el paciente pide renovar un tratamiento que ya se hizo en la clínica.
 * Recepción o el médico la agenda (crea la cita) o la cancela.
 */
class ServiceRequest extends Model
{
    use BelongsToClinic;

    public const STATUSES = [
        'pending' => 'Pendiente',
        'scheduled' => 'Agendada',
        'completed' => 'Atendida',
        'cancelled' => 'Cancelada',
    ];

    public const TIMES = [
        'manana' => 'Mañana (8:00 a 12:00)',
        'tarde' => 'Tarde (14:00 a 18:00)',
    ];

    /** Lo que el paciente envía. clinic_id, patient_id, estado y gestión se fijan en código. */
    protected $fillable = [
        'preferred_date', 'preferred_time', 'message',
    ];

    protected $attributes = [
        'status' => 'pending',
        'type' => 'renewal',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
        ];
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'scheduled'], true);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(PatientTreatment::class, 'patient_treatment_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
