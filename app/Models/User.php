<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'password',
        'is_super_admin',
        'role',
    ];

    /** Igual al default de la columna (el modelo recién creado no lo lee de la base). */
    protected $attributes = [
        'role' => self::ROLE_ADMIN,
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_RECEPTION = 'reception';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_RECEPTION => 'Recepción',
        self::ROLE_DOCTOR => 'Médico',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Perfil de médico vinculado (panel /medico). */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class)->withoutGlobalScope('clinic');
    }

    public function isClinicAdmin(): bool
    {
        return $this->is_super_admin || $this->role === self::ROLE_ADMIN;
    }

    public function isReception(): bool
    {
        return $this->role === self::ROLE_RECEPTION;
    }

    /** Perfil de médico activo de su clínica, si lo tiene. */
    public function activeDoctor(): ?Doctor
    {
        $doctor = $this->doctor;

        return $doctor && $doctor->is_active && (int) $doctor->clinic_id === (int) $this->clinic_id ? $doctor : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            // Administración: admins y recepción (un médico solo entra a /medico).
            'admin' => $this->is_super_admin
                || ($this->clinic_id !== null && in_array($this->role, [self::ROLE_ADMIN, self::ROLE_RECEPTION], true)),
            // Panel médico: cualquier usuario con un perfil de médico activo en su clínica.
            'doctor' => $this->clinic_id !== null && $this->activeDoctor() !== null,
            default => false,
        };
    }
}
