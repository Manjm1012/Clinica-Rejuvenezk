<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Paciente del portal. Usa su propio guard ("patient"): NO es un User,
 * por lo que nunca puede entrar al panel /admin.
 */
class Patient extends Authenticatable
{
    use Auditable, BelongsToClinic;

    /** Versión del texto de la política de datos que acepta el registro. */
    public const DATA_POLICY_VERSION = '2026-11';

    /**
     * clinic_id, lead_id y las fechas de consentimiento no son asignables en masa:
     * se fijan explícitamente en el código, nunca desde el request.
     */
    protected $fillable = [
        'name', 'email', 'phone', 'birth_date', 'password', 'marketing_consent',
        'document_type', 'document_number', 'gender',
        'allergies', 'medical_history', 'current_medications',
    ];

    /** Datos clínicos: nunca salen en una serialización (API, JSON, logs). */
    protected $hidden = [
        'password', 'remember_token', 'allergies', 'medical_history', 'current_medications',
    ];

    public const DOCUMENT_TYPES = [
        'CC' => 'Cédula de ciudadanía',
        'CE' => 'Cédula de extranjería',
        'TI' => 'Tarjeta de identidad',
        'PA' => 'Pasaporte',
        'PPT' => 'Permiso por protección temporal',
    ];

    public const GENDERS = [
        'F' => 'Femenino',
        'M' => 'Masculino',
        'O' => 'Otro / prefiere no decir',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
            'data_policy_accepted_at' => 'datetime',
            'allergies' => 'encrypted',
            'medical_history' => 'encrypted',
            'current_medications' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        // Si cambia el consentimiento (portal o Filament) y nadie fijó la fecha, se registra ahora.
        static::saving(function (Patient $patient): void {
            if ($patient->isDirty('marketing_consent') && ! $patient->isDirty('marketing_consent_at')) {
                $patient->marketing_consent_at = now();
            }
        });
    }

    /** Solo dígitos; quita el indicativo 57 si quedan 12 dígitos (móvil colombiano). */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '57')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = static::normalizePhone($value);
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = static::normalizeEmail($value);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(PatientTreatment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /** La historia clínica no se elimina: un paciente con consultas o fórmulas no se puede borrar. */
    public function hasClinicalRecords(): bool
    {
        return Consultation::query()->withoutGlobalScope('clinic')->where('patient_id', $this->getKey())->exists()
            || Prescription::query()->withoutGlobalScope('clinic')->where('patient_id', $this->getKey())->exists();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
