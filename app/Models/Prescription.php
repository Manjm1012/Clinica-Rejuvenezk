<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToClinic;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

/**
 * Fórmula médica: indicaciones y cuidados posteriores + medicamentos estéticos.
 * Borrador -> Emitida (inmodificable, visible para el paciente) -> Anulada (con motivo).
 */
class Prescription extends Model
{
    use Auditable, BelongsToClinic;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_ANNULLED = 'annulled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_ISSUED => 'Emitida',
        self::STATUS_ANNULLED => 'Anulada',
    ];

    /** Lo único que puede cambiar en una fórmula emitida: su anulación. */
    private const ANNUL_FIELDS = ['status', 'annulled_at', 'annulled_reason', 'updated_at'];

    protected $fillable = [
        'consultation_id', 'diagnosis', 'care_instructions', 'valid_until',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'annulled_at' => 'datetime',
            'valid_until' => 'date',
            'diagnosis' => 'encrypted',
            'care_instructions' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Prescription $prescription): void {
            $original = $prescription->getOriginal('status');

            if ($original === self::STATUS_ANNULLED) {
                throw new LogicException('Una fórmula anulada no se puede modificar.');
            }

            if ($original === self::STATUS_ISSUED && array_diff(array_keys($prescription->getDirty()), self::ANNUL_FIELDS) !== []) {
                throw new LogicException('Una fórmula emitida no se puede modificar; anúlala y crea una nueva.');
            }
        });

        static::deleting(function (Prescription $prescription): void {
            if ($prescription->getOriginal('status') !== self::STATUS_DRAFT) {
                throw new LogicException('Solo se pueden eliminar fórmulas en borrador.');
            }
        });
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function issue(): void
    {
        if (! $this->isDraft()) {
            return;
        }

        if (! $this->items()->exists() && blank($this->care_instructions)) {
            throw new LogicException('Agrega al menos un medicamento o las indicaciones antes de emitir.');
        }

        $this->status = self::STATUS_ISSUED;
        $this->issued_at = now();
        $this->code = 'F-'.$this->clinic_id.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        $this->saveQuietly();

        Audit::log('issued', $this);
    }

    public function annul(string $reason): void
    {
        if (! $this->isIssued()) {
            return;
        }

        $this->status = self::STATUS_ANNULLED;
        $this->annulled_at = now();
        $this->annulled_reason = mb_substr($reason, 0, 500);
        $this->saveQuietly();

        Audit::log('annulled', $this);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
