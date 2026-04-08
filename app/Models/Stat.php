<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'label', 'value', 'icon', 'color', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
