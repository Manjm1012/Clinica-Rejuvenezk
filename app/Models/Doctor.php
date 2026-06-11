<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'specialty', 'subtitle', 'photo_path', 'bio', 'university',
        'certifications', 'linkedin_url', 'instagram_url', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function credentials(): HasMany
    {
        return $this->hasMany(DoctorCredential::class)->orderBy('sort_order');
    }
}
