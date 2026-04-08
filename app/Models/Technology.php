<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'description', 'image_path', 'logo_path', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
