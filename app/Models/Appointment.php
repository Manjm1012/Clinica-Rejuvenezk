<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'lead_id', 'service_id', 'patient_name', 'patient_phone', 'patient_email',
        'appointment_at', 'status', 'notes', 'tayrai_appointment_id',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'   => 'Pendiente',
        'confirmed' => 'Confirmado',
        'cancelled' => 'Cancelado',
        'completed' => 'Completado',
        'no_show'   => 'No asistió',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
