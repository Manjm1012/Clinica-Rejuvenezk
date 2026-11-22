<?php

namespace App\View;

use App\Models\GalleryItem;
use App\Support\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Datos derivados de la portada (antes: 400 líneas de @php dentro de home.blade.php).
 * Solo transforma lo que el controlador ya consultó; no hace consultas propias.
 */
class HomePage
{
    public static function build(array $data): array
    {
        $settings = $data['settings'];
        $doctor = $data['doctor'];
        $galleryItems = $data['galleryItems'];
        $featured = $data['featuredServices']->first();

        $doctorPhotoUrl = Media::url($doctor?->photo_path);
        $featuredImage = Media::url($featured?->banner_path) ?? Media::url($featured?->image_path);
        $clinicLogoUrl = Media::url($data['clinic']?->logo_path);

        return [
            'hero' => [
                'kicker' => $settings['hero_kicker'] ?? 'Medicina estética avanzada',
                'title' => $settings['hero_title'] ?? 'Rejuvenecimiento facial con resultados naturales.',
                'subtitle' => Str::limit(trim((string) ($settings['hero_subtitle'] ?? '')), 210, '...'),
                'primaryCta' => $settings['hero_primary_cta'] ?? 'Reservar valoración',
                'image' => $doctorPhotoUrl,
            ],
            'doctorPhotoUrl' => $doctorPhotoUrl,
            'trustItems' => static::trustItems($settings, $data['stats']),
            'promoBanners' => static::promoBanners($settings, [$doctorPhotoUrl, $featuredImage, Media::resolve($settings['hero_banner_image'] ?? null), $clinicLogoUrl]),
            'beforeAfter' => static::beforeAfter($galleryItems, 4),
            'gallery' => static::gallery($galleryItems, 4),
            'videos' => static::videos($galleryItems, 6),
            'texts' => static::texts($settings),
        ];
    }

    /** Textos editables de la portada (Ajustes → branding), con sus valores por defecto. */
    public static function texts(array $settings): array
    {
        return [
            'servicesKicker' => $settings['services_kicker'] ?? 'Nuestros tratamientos',
            'servicesTitle' => $settings['services_title'] ?? 'Tratamientos seleccionados con enfoque médico estético',
            'servicesLead' => $settings['services_lead'] ?? 'Cada protocolo combina diagnóstico, técnica y naturalidad para construir resultados elegantes, progresivos y coherentes con tu rostro.',
            'testimonialsKicker' => static::testimonialsKicker($settings),
            'testimonialsTitle' => $settings['testimonials_title'] ?? 'Resultados y testimonios reales',
            'resultsTitle' => $settings['results_title'] ?? 'Imágenes que capturan el tipo de cambio que buscan nuestros pacientes.',
            'resultsLead' => $settings['results_lead'] ?? 'Tratamientos, armonización y resultados reales en una sola mirada.',
            'ctaKicker' => $settings['cta_kicker'] ?? 'Agenda tu cita',
            'ctaTitle' => $settings['cta_title'] ?? 'Empieza tu valoración con una experiencia clara y personalizada.',
            'ctaBody' => $settings['cta_body'] ?? 'Atención médica estética, seguimiento ordenado y una propuesta pensada para tus objetivos reales.',
            'ctaNote' => $settings['cta_note'] ?? 'Respuesta por WhatsApp o correo con orientación inicial y siguientes pasos.',
            'ctaWhatsappLabel' => $settings['cta_whatsapp_label'] ?? 'WhatsApp',
            'ctaEmailLabel' => $settings['cta_email_label'] ?? 'Solicitar información',
        ];
    }

    /** Franja de confianza: estadísticas reales; si no hay, textos editables (branding.trust_item_N). */
    public static function trustItems(array $settings, Collection $stats): Collection
    {
        $icons = ['shield', 'doctor', 'spark', 'heart'];

        if ($stats->isNotEmpty()) {
            return $stats->take(4)->values()->map(fn ($stat, $i) => ['value' => $stat->value, 'label' => $stat->label, 'icon' => $icons[$i % 4]]);
        }

        return collect([
            $settings['trust_item_1'] ?? 'Valoración sin costo',
            $settings['trust_item_2'] ?? 'Atención por médico certificado',
            $settings['trust_item_3'] ?? 'Resultados naturales',
            $settings['trust_item_4'] ?? 'Seguimiento personalizado',
        ])->filter(fn ($text) => trim((string) $text) !== '')->values()
            ->map(fn ($text, $i) => ['value' => null, 'label' => $text, 'icon' => $icons[$i % 4]]);
    }

    /** Banners promocionales (editables en Configuración → Banners Home), con imágenes de respaldo. */
    public static function promoBanners(array $settings, array $fallbackImages): Collection
    {
        $fallbacks = collect($fallbackImages)->filter()->values();

        return collect([
            ['kicker' => 'Medicina estética', 'title' => 'Resultados naturales con criterio médico', 'text' => 'Valoración personalizada y protocolos diseñados para tu rostro.', 'key' => 'home_banner_1_image'],
            ['kicker' => 'Tecnología avanzada', 'title' => 'Tratamientos seguros y progresivos', 'text' => 'Combinamos diagnóstico, precisión clínica y seguimiento cercano.', 'key' => 'home_banner_2_image'],
            ['kicker' => 'Atención profesional', 'title' => 'Plan integral para armonizar tu belleza', 'text' => 'Una experiencia estética clara, elegante y enfocada en confianza.', 'key' => 'home_banner_3_image'],
        ])->map(fn (array $banner, int $i) => [
            ...$banner,
            'image' => Media::resolve($settings[$banner['key']] ?? null) ?? $fallbacks[$i] ?? $fallbacks->first(),
        ]);
    }

    public static function beforeAfter(Collection $items, int $limit): Collection
    {
        return $items->map(fn (GalleryItem $item) => Media::beforeAfter($item))->filter()->take($limit)->values();
    }

    public static function gallery(Collection $items, int $limit): Collection
    {
        return $items->map(function (GalleryItem $item) {
            $url = Media::galleryImage($item);

            return $url ? [
                'url' => $url,
                'title' => $item->title ?: ($item->service?->name ?? 'Resultado'),
                'label' => $item->service?->name ?: 'Caso destacado',
            ] : null;
        })->filter()->take($limit)->values();
    }

    public static function videos(Collection $items, int $limit): Collection
    {
        return $items
            ->filter(fn (GalleryItem $item) => $item->type === 'video' && filled($item->video_url))
            ->map(fn (GalleryItem $item) => Media::video($item))
            ->filter()
            ->take($limit)
            ->values();
    }

    private static function testimonialsKicker(array $settings): string
    {
        $raw = trim((string) ($settings['testimonials_kicker'] ?? ''));

        return $raw === '' || in_array(mb_strtolower($raw), ['testimonios', 'testimonio'], true) ? 'Resultados' : $raw;
    }
}
