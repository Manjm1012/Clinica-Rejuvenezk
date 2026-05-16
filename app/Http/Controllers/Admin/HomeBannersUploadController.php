<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeBannersUploadController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (!Auth::check() || !(bool) Auth::user()?->is_super_admin) {
            abort(403);
        }

        $request->validate([
            'home_banner_1_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'home_banner_2_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'home_banner_3_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $payload = [
            'home_banner_1_image' => SiteSetting::get('branding', 'home_banner_1_image', ''),
            'home_banner_2_image' => SiteSetting::get('branding', 'home_banner_2_image', ''),
            'home_banner_3_image' => SiteSetting::get('branding', 'home_banner_3_image', ''),
        ];

        foreach (array_keys($payload) as $field) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            if (!$file || !$file->isValid()) {
                continue;
            }

            $oldPath = $payload[$field] ?: null;
            $extension = $file->getClientOriginalExtension() ?: 'webp';
            $newPath = 'branding/banners/' . Str::ulid() . '.' . strtolower($extension);

            Storage::disk('public')->putFileAs('branding/banners', $file, basename($newPath), [
                'visibility' => 'public',
            ]);

            $payload[$field] = $newPath;

            if ($oldPath && str_starts_with($oldPath, 'branding/banners/') && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        SiteSetting::set('branding', 'home_banner_1_image', $payload['home_banner_1_image'], 'image', 'Imagen banner home 1');
        SiteSetting::set('branding', 'home_banner_2_image', $payload['home_banner_2_image'], 'image', 'Imagen banner home 2');
        SiteSetting::set('branding', 'home_banner_3_image', $payload['home_banner_3_image'], 'image', 'Imagen banner home 3');

        return redirect('/admin/home-banners')->with('status', 'Banners actualizados correctamente.');
    }
}
