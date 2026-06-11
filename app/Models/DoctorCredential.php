<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorCredential extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'doctor_id', 'type', 'title', 'institution', 'year', 'sort_order',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
