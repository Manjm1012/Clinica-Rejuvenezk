<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_name', 'patient_avatar_path', 'content', 'rating',
        'source', 'source_url', 'reviewed_at', 'is_featured', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
        'reviewed_at' => 'date',
    ];

    /**
     * Reseñas que se pueden mostrar al público: activas y con enlace a la opinión original
     * (source_url). Sin enlace no se puede verificar, y así quedan fuera los textos de
     * ejemplo que sembraban versiones antiguas del seeder ("Paciente Demo").
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('source_url')
            ->where('source_url', '!=', '');
    }

    /** Mínimo de reseñas verificables para publicar un promedio (con 1 o 2 reseñas un "5,0/5" no dice nada). */
    public const MIN_REVIEWS_FOR_RATING = 10;

    /**
     * Valoración pública: promedio y número de reseñas reales verificables.
     * Verificable = activa, con calificación y con enlace a la opinión original (source_url).
     * Devuelve null mientras no se alcance MIN_REVIEWS_FOR_RATING.
     *
     * @return array{average: string, count: int}|null
     */
    public static function publicRating(): ?array
    {
        $query = static::query()->published()->whereNotNull('rating');

        $count = (clone $query)->count();

        if ($count < self::MIN_REVIEWS_FOR_RATING) {
            return null;
        }

        return [
            'average' => number_format((float) $query->avg('rating'), 1, ',', ''),
            'count' => $count,
        ];
    }
}
