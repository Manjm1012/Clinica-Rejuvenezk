<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToClinic;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/**
 * Consulta médica (historia clínica). Una vez firmada es inmodificable:
 * las correcciones se hacen con notas aclaratorias (ConsultationAddendum).
 */
class Consultation extends Model
{
    use Auditable, BelongsToClinic;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SIGNED = 'signed';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_SIGNED => 'Firmada',
    ];

    /** clinic_id, patient_id, doctor_id y el estado se fijan en código, nunca desde el formulario. */
    protected $fillable = [
        'service_id', 'consulted_at', 'reason', 'findings', 'diagnosis', 'plan',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'consulted_at' => 'datetime',
            'signed_at' => 'datetime',
            'reason' => 'encrypted',
            'findings' => 'encrypted',
            'diagnosis' => 'encrypted',
            'plan' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Consultation $consultation): void {
            if ($consultation->getOriginal('status') === self::STATUS_SIGNED) {
                throw new LogicException('Una consulta firmada no se puede modificar. Agrega una nota aclaratoria.');
            }
        });

        static::deleting(function (Consultation $consultation): void {
            if ($consultation->getOriginal('status') === self::STATUS_SIGNED) {
                throw new LogicException('Una consulta firmada no se puede eliminar.');
            }
        });
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function sign(): void
    {
        if ($this->isSigned()) {
            return;
        }

        $this->status = self::STATUS_SIGNED;
        $this->signed_at = now();
        $this->saveQuietly(); // sin el evento "updated": se audita como "signed"

        Audit::log('signed', $this);
    }

    public function addAddendum(string $body, ?User $author): ConsultationAddendum
    {
        $addendum = new ConsultationAddendum(['body' => $body]);
        $addendum->consultation_id = $this->getKey();
        $addendum->user_id = $author?->getKey();
        $addendum->save();

        Audit::log('addendum', $this);

        return $addendum;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addenda(): HasMany
    {
        return $this->hasMany(ConsultationAddendum::class)->orderBy('created_at');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
