<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Stat;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class PublicContentController extends Controller
{
    public function home(): JsonResponse
    {
        return response()->json([
            'branding' => [
                'clinic_name' => SiteSetting::get('branding', 'clinic_name', 'Clínica Rejuvenezk'),
                'hero_title' => SiteSetting::get('branding', 'hero_title', ''),
                'hero_subtitle' => SiteSetting::get('branding', 'hero_subtitle', ''),
            ],
            'contact' => [
                'whatsapp_number' => SiteSetting::get('contact', 'whatsapp_number', ''),
                'phone' => SiteSetting::get('contact', 'phone', ''),
                'email' => SiteSetting::get('contact', 'email', ''),
                'address' => SiteSetting::get('contact', 'address', ''),
            ],
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'doctor' => Doctor::query()->where('is_active', true)->first(),
            'stats' => Stat::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'technologies' => Technology::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->get(),
        ]);
    }

    public function service(Service $service): JsonResponse
    {
        return response()->json($service->load(['category', 'faqs', 'galleryItems']));
    }
}
