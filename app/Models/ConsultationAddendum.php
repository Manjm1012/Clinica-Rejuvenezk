<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Nota aclaratoria sobre una consulta firmada. Solo se agregan, nunca se editan ni borran. */
class ConsultationAddendum extends Model
{
    protected $table = 'consultation_addenda';

    protected $fillable = ['body'];

    protected function casts(): array
    {
        return ['body' => 'encrypted'];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Las notas aclaratorias no se modifican.');
        });

        static::deleting(function (): void {
            throw new LogicException('Las notas aclaratorias no se eliminan.');
        });
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
