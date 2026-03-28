<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Stat;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'settings' => [
                'clinic_name' => SiteSetting::get('branding', 'clinic_name', 'Clínica Rejuvenezk'),
                'hero_title' => SiteSetting::get('branding', 'hero_title', 'Cirugía y medicina estética premium'),
                'hero_subtitle' => SiteSetting::get('branding', 'hero_subtitle', 'Sitio administrable y comercializable para clínicas.'),
                'whatsapp_number' => SiteSetting::get('contact', 'whatsapp_number', ''),
                'phone' => SiteSetting::get('contact', 'phone', ''),
                'email' => SiteSetting::get('contact', 'email', ''),
                'address' => SiteSetting::get('contact', 'address', ''),
                'instagram_url' => SiteSetting::get('social', 'instagram_url', ''),
                'youtube_url' => SiteSetting::get('social', 'youtube_url', ''),
            ],
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'featuredServices' => Service::query()->where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->limit(6)->get(),
            'doctor' => Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(),
            'stats' => Stat::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'technologies' => Technology::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get(),
        ]);
    }
}
