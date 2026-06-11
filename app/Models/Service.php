<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'service_category_id', 'name', 'slug', 'short_description', 'description',
        'image_path', 'banner_path', 'whatsapp_text', 'is_featured', 'is_premium',
        'is_active', 'sort_order', 'meta',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_premium'  => 'boolean',
        'is_active'   => 'boolean',
        'meta'        => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getWhatsappUrlAttribute(): string
    {
        $rawUrl = trim((string) SiteSetting::get('contact', 'whatsapp_url', ''));
        $phone = SiteSetting::get('contact', 'whatsapp_number', '');
        $text  = $this->whatsapp_text ?? 'Hola! Estoy interesado/a en ' . $this->name;

        if ($rawUrl !== '') {
            $separator = str_contains($rawUrl, '?') ? '&' : '?';
            return $rawUrl . $separator . 'text=' . urlencode($text);
        }

        return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) . '?text=' . urlencode($text);
    }
}
