<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenant;

class ClinicalProcedure extends Model
{
    use HasFactory;
    use HasTenant;

    protected static function booted(): void
    {
        static::creating(function (ClinicalProcedure $procedure) {
            if (empty($procedure->client_id) && $procedure->medical_history_id) {
                $history = MedicalHistory::find($procedure->medical_history_id);
                if ($history) {
                    $procedure->client_id = $history->client_id;
                }
            }
        });
    }

    protected $fillable = [
        'client_id',
        'medical_history_id',
        'name',
        'notes',
        'amount',
        'performed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'performed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function medicalHistory(): BelongsTo
    {
        return $this->belongsTo(MedicalHistory::class);
    }
}
