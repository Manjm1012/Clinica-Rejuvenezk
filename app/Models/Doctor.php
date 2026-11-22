<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'specialty', 'subtitle', 'photo_path', 'bio', 'university',
        'certifications', 'linkedin_url', 'instagram_url', 'is_active', 'sort_order',
        'professional_registration', 'signature_path',
    ];

    /** Datos internos: nunca salen en la API pública ni en serializaciones. */
    protected $hidden = ['user_id', 'signature_path'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function credentials(): HasMany
    {
        return $this->hasMany(DoctorCredential::class)->orderBy('sort_order');
    }

    /** Cuenta de acceso al panel médico (se asigna desde Usuarios, no es asignable en masa). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
