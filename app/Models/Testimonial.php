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
}
