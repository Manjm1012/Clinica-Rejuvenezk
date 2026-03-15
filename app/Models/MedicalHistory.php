<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenant;

class MedicalHistory extends Model
{
    use HasFactory;
    use HasTenant;

    protected static function booted(): void
    {
        static::creating(function (MedicalHistory $history) {
            if (empty($history->client_id) && $history->patient_id) {
                $patient = Patient::find($history->patient_id);
                if ($patient) {
                    $history->client_id = $patient->client_id;
                }
            }
        });
    }

    protected $fillable = [
        'client_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'reason_for_consultation',
        'diagnosis',
        'treatment_plan',
        'consulted_at',
    ];

    protected $casts = [
        'consulted_at' => 'datetime',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(ClinicalProcedure::class);
    }
}
