<?php

namespace App\Support;

use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;

/**
 * Resolución de archivos multimedia del disco "public" (logos, fotos, banners, galería).
 * Antes esta lógica estaba copiada en cada vista.
 */
class Media
{
    /** Poster genérico para videos sin miniatura (SVG embebido). */
    private const VIDEO_POSTER = 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 1920 1080%27%3E%3Cdefs%3E%3ClinearGradient id=%27g%27 x1=%270%25%27 y1=%270%25%27 x2=%27100%25%27 y2=%27100%25%27%3E%3Cstop offset=%270%25%27 stop-color=%27%230f0f10%27/%3E%3Cstop offset=%2745%25%27 stop-color=%27%23221b1a%27/%3E%3Cstop offset=%27100%25%27 stop-color=%27%239b8860%27/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width=%271920%27 height=%271080%27 fill=%27url(%23g)%27/%3E%3Ccircle cx=%27960%27 cy=%27540%27 r=%27170%27 fill=%27white%27 opacity=%270.12%27/%3E%3Cpolygon points=%27924,466 924,614 1044,540%27 fill=%27white%27 opacity=%270.85%27/%3E%3C/svg%3E';

    /** Ruta relativa al disco public, sin prefijos "storage/", "media/"... ni dominio. */
    public static function normalize(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = trim($path);

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            $normalized = parse_url($normalized, PHP_URL_PATH) ?: $normalized;
        }

        $normalized = ltrim(rawurldecode($normalized), '/');

        foreach (['media/', 'storage/', 'public/', 'app/public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return $normalized !== '' ? $normalized : null;
    }

    /** URL pública de un archivo del disco, o null si no existe. */
    public static function url(?string $path): ?string
    {
        $normalized = static::normalize($path);

        if (! $normalized) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($normalized) ? $disk->url($normalized) : null;
    }

    /** Como url(), pero deja pasar URLs absolutas externas (p. ej. una imagen en un CDN). */
    public static function resolve(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) && ! str_contains($value, '/storage/') && ! str_contains($value, '/media/')
            ? $value
            : static::url($value);
    }

    /** Primera imagen existente de un elemento de galería. */
    public static function galleryImage(GalleryItem $item): ?string
    {
        foreach ([$item->image_path, $item->after_image_path, $item->before_image_path] as $candidate) {
            if ($url = static::url($candidate)) {
                return $url;
            }
        }

        return null;
    }

    /** Par antes/después si ambas imágenes existen. */
    public static function beforeAfter(GalleryItem $item): ?array
    {
        $before = static::url($item->before_image_path);
        $after = static::url($item->after_image_path);

        if (! $before || ! $after) {
            return null;
        }

        return [
            'before' => $before,
            'after' => $after,
            'title' => $item->title ?: ($item->service?->name ?? 'Resultado'),
            'label' => $item->service?->name,
        ];
    }

    /** Video de galería: YouTube/Vimeo como iframe; archivo propio como <video>. */
    public static function video(GalleryItem $item): ?array
    {
        $raw = trim((string) $item->video_url);

        if ($raw === '') {
            return null;
        }

        $base = [
            'title' => $item->title ?: ($item->service?->name ?? 'Video de resultado'),
            'label' => $item->service?->name ?: 'Caso real',
        ];

        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return $base + ['url' => Storage::disk('public')->url(ltrim($raw, '/')), 'is_iframe' => false, 'poster' => self::VIDEO_POSTER];
        }

        $host = strtolower((string) parse_url($raw, PHP_URL_HOST));

        if (str_contains($host, 'youtu.be') || str_contains($host, 'youtube.com')) {
            if (str_contains($host, 'youtu.be')) {
                $videoId = trim((string) parse_url($raw, PHP_URL_PATH), '/');
            } else {
                parse_str((string) parse_url($raw, PHP_URL_QUERY), $query);
                $videoId = $query['v'] ?? null;
            }

            if ($videoId && preg_match('/^[\w-]{6,20}$/', $videoId)) {
                return $base + ['url' => 'https://www.youtube-nocookie.com/embed/'.$videoId.'?controls=1&rel=0&playsinline=1', 'is_iframe' => true];
            }
        }

        if (str_contains($host, 'vimeo.com')) {
            $videoId = trim((string) parse_url($raw, PHP_URL_PATH), '/');

            if ($videoId && ctype_digit($videoId)) {
                return $base + ['url' => 'https://player.vimeo.com/video/'.$videoId.'?title=0&byline=0&portrait=0&playsinline=1', 'is_iframe' => true];
            }
        }

        return $base + ['url' => $raw, 'is_iframe' => false, 'poster' => self::VIDEO_POSTER];
    }

    /** Iniciales para monogramas ("Doctor Kevin" -> "DK"). */
    public static function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($segment) => mb_strtoupper(mb_substr($segment, 0, 1)))
            ->implode('') ?: 'DK';
    }
}
