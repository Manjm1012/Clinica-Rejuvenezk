<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Registro de auditoría (solo inserción). Guarda quién, qué y cuándo; nunca valores clínicos.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public const ACTIONS = [
        'viewed' => 'Consultó',
        'created' => 'Creó',
        'updated' => 'Modificó',
        'signed' => 'Firmó',
        'issued' => 'Emitió',
        'annulled' => 'Anuló',
        'printed' => 'Imprimió',
        'addendum' => 'Agregó nota aclaratoria',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // La auditoría no se modifica ni se borra desde la aplicación.
    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }
}
