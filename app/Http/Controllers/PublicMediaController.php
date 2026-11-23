<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve archivos multimedia del disco "public".
 * Solo extensiones de imagen/video/PDF conocidas; nunca archivos ocultos ni subidas temporales.
 */
class PublicMediaController extends Controller
{
    private const MIME_TYPES = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        'm4v' => 'video/mp4',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
    ];

    /** Carpetas que nunca se exponen (subidas sin validar). */
    private const BLOCKED_PREFIXES = ['livewire-tmp/'];

    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));

        abort_if(
            $normalizedPath === ''
            || str_contains($normalizedPath, '..')
            || str_contains('/'.$normalizedPath, '/.')
            || ! isset(self::MIME_TYPES[$extension])
            || collect(self::BLOCKED_PREFIXES)->contains(fn (string $prefix) => str_starts_with($normalizedPath, $prefix)),
            404
        );

        $disk = Storage::disk('public');

        abort_unless($disk->exists($normalizedPath), 404);

        $headers = [
            'Content-Type' => self::MIME_TYPES[$extension],
            'Cache-Control' => 'public, max-age=86400',
            'Accept-Ranges' => 'bytes',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // Un SVG puede llevar scripts: se sirve aislado.
        if ($extension === 'svg') {
            $headers['Content-Security-Policy'] = "default-src 'none'; style-src 'unsafe-inline'; sandbox";
        }

        return response()->file($disk->path($normalizedPath), $headers);
    }
}
