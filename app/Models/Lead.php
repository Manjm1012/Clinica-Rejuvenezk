<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'phone', 'email', 'message', 'service_id', 'source',
        'utm_source', 'utm_medium', 'utm_campaign', 'status',
        'tayrai_lead_id', 'tayrai_contact_id', 'notes', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public const STATUSES = [
        'new'              => 'Nuevo',
        'contacted'        => 'Contactado',
        'qualified'        => 'Calificado',
        'appointment_set'  => 'Cita Agendada',
        'converted'        => 'Convertido',
        'lost'             => 'Perdido',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
