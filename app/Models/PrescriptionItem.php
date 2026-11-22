<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'medication', 'concentration', 'dosage_form', 'route', 'dose',
        'frequency', 'duration', 'quantity', 'instructions', 'sort_order',
    ];

    protected static function booted(): void
    {
        // Los medicamentos de una fórmula emitida o anulada no se tocan.
        $guard = function (PrescriptionItem $item): void {
            $status = Prescription::query()->withoutGlobalScope('clinic')->whereKey($item->prescription_id)->value('status');

            if ($status && $status !== Prescription::STATUS_DRAFT) {
                throw new LogicException('La fórmula ya fue emitida: sus medicamentos no se pueden cambiar.');
            }
        };

        static::creating($guard);
        static::updating($guard);
        static::deleting($guard);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
