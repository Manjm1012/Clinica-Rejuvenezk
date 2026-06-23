<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\GalleryItem;

class DiagnoseVideoUpload extends Command
{
    protected $signature = 'diagnose:video-upload {--video-id=}';
    protected $description = 'Diagnose why uploaded videos are not displaying';

    public function handle(): int
    {
        $this->info('🔍 Video Upload Diagnostic Tool');
        $this->line('');

        // 1. Check public disk configuration
        $this->info('📋 Configuration Check:');
        $publicDisk = Storage::disk('public');
        $this->line("   Root path: " . Storage::disk('public')->path(''));
        $this->line("   App URL: " . config('app.url'));
        $this->line("   Media URL base: " . config('app.url') . '/media');
        $this->line('');

        // 2. List all video files in storage
        $this->info('📁 Files in public storage:');
        $allFiles = Storage::disk('public')->allFiles();
        $videoFiles = array_filter($allFiles, fn($f) => preg_match('/\.(mp4|webm|mov|m4v)$/i', $f));
        
        if (empty($videoFiles)) {
            $this->warn('   ⚠️  No video files found in storage!');
        } else {
            foreach ($videoFiles as $file) {
                $size = Storage::disk('public')->size($file);
                $readableSize = $this->formatBytes($size);
                $this->line("   ✓ {$file} ({$readableSize})");
            }
        }
        $this->line('');

        // 3. Check database records
        $this->info('🗄️  Database Video Records:');
        $videos = GalleryItem::where('type', 'video')->get();
        
        if ($videos->isEmpty()) {
            $this->warn('   ⚠️  No video records in database!');
        } else {
            foreach ($videos as $video) {
                $this->line("   ID: {$video->id}");
                $this->line("   Video URL: {$video->video_url}");
                $this->line("   Full media URL: " . config('app.url') . "/media/{$video->video_url}");
                
                // Check if file exists
                $exists = Storage::disk('public')->exists($video->video_url);
                $status = $exists ? '✓ EXISTS' : '✗ NOT FOUND';
                $this->line("   File status: {$status}");
                
                if ($exists) {
                    $size = Storage::disk('public')->size($video->video_url);
                    $this->line("   File size: " . $this->formatBytes($size));
                }
                $this->line('');
            }
        }

        // 4. Test URL accessibility
        $this->info('🌐 URL Accessibility Test:');
        if (!$videos->isEmpty()) {
            $firstVideo = $videos->first();
            $testUrl = config('app.url') . "/media/{$firstVideo->video_url}";
            $this->line("   Testing URL: {$testUrl}");
            $this->line("   To verify: Open this URL in browser or use curl:");
            $this->line("   $ curl -I '{$testUrl}'");
            $this->line('');
        }

        // 5. Check symlink
        $this->info('🔗 Storage Symlink Status:');
        if (is_link(public_path('storage'))) {
            $target = readlink(public_path('storage'));
            $this->line("   ✓ Symlink exists: " . public_path('storage') . " → {$target}");
        } else {
            $this->warn("   ⚠️  Symlink does not exist!");
            $this->line("   To create it, run: php artisan storage:link");
        }
        $this->line('');

        // 6. Check file permissions
        $this->info('🔐 File Permissions:');
        $storageDir = storage_path('app/public');
        if (is_dir($storageDir)) {
            $perms = substr(sprintf('%o', fileperms($storageDir)), -4);
            $this->line("   storage/app/public permissions: {$perms}");
        }
        $this->line('');

        // 7. Browser Console Test
        $this->info('🧪 Browser Console Test:');
        if (!$videos->isEmpty()) {
            $firstVideo = $videos->first();
            $testUrl = config('app.url') . "/media/{$firstVideo->video_url}";
            $this->line("   In browser F12 console, run:");
            $this->line("   fetch('{$testUrl}').then(r => console.log(r.status, r.headers.get('content-type')))");
            $this->line('');
        }

        $this->info('✅ Diagnostic complete!');
        return self::SUCCESS;
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
