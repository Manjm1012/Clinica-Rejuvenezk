<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenant;

class Appointment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'client_id',
        'patient_id',
        'doctor_id',
        'whatsapp_conversation_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public static array $statuses = [
        'pending'     => 'Pendiente',
        'confirmed'   => 'Confirmada',
        'in_progress' => 'En progreso',
        'completed'   => 'Completada',
        'cancelled'   => 'Cancelada',
    ];

    public static array $statusColors = [
        'pending'     => 'warning',
        'confirmed'   => 'success',
        'in_progress' => 'info',
        'completed'   => 'success',
        'cancelled'   => 'danger',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function whatsappConversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class);
    }
}
