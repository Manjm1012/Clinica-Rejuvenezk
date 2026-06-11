<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicMediaController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim($path, '/');

        abort_if($normalizedPath === '' || str_contains($normalizedPath, '..'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($normalizedPath), 404);

        return response()->file($disk->path($normalizedPath), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}