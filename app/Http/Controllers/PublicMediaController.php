<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicMediaController extends Controller
{
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim($path, '/');

        abort_if($normalizedPath === '' || str_contains($normalizedPath, '..'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($normalizedPath), 404);

        $filePath = $disk->path($normalizedPath);
        $mimeType = $this->getMimeType($normalizedPath);
        
        $response = response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
            'Accept-Ranges' => 'bytes',
        ]);
        
        return $response;
    }
    
    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'm4v' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}