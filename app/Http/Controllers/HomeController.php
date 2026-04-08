<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\Stat;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $branding = array_merge([
            'clinic_name' => 'Clínica Rejuvenezk',
            'hero_kicker' => 'Medicina estética avanzada',
            'hero_title' => 'Cirugía y medicina estética premium',
            'hero_subtitle' => 'Sitio administrable y comercializable para clínicas.',
            'hero_primary_cta' => 'Reservar valoración',
            'hero_secondary_cta' => 'Ver tratamientos',
            'hero_card_kicker' => 'Primera cita',
            'hero_card_title' => 'Diagnóstico inteligente de piel',
            'hero_card_text' => 'Análisis de calidad cutánea y plan estético por etapas para resultados armónicos y progresivos.',
            'hero_stack_primary_label' => 'Protocolo estrella',
            'hero_stack_secondary_label' => 'Especialista',
            'services_kicker' => 'Nuestros tratamientos',
            'services_title' => 'Tecnología, precisión y naturalidad',
            'testimonials_kicker' => 'Testimonios',
            'testimonials_title' => 'Pacientes que ya viven su cambio',
            'social_kicker' => 'Comunidad Rejuvenezk',
            'social_title' => 'Conecta con nuestras redes y canales',
            'social_lead' => 'Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.',
            'cta_kicker' => 'Agenda tu cita',
            'cta_title' => 'Empieza hoy tu protocolo personalizado.',
            'cta_body' => 'Atención presencial y seguimiento digital para una experiencia comercial ordenada.',
            'topbar_cta_label' => 'Agenda ahora',
            'cta_whatsapp_label' => 'WhatsApp',
            'cta_email_label' => 'Solicitar información',
        ], SiteSetting::group('branding'));

        $contact = array_merge([
            'whatsapp_url' => '',
            'whatsapp_number' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
        ], SiteSetting::group('contact'));

        $social = array_merge([
            'instagram_url' => '',
            'facebook_url' => '',
            'tiktok_url' => '',
            'youtube_url' => '',
        ], SiteSetting::group('social'));

        return view('home', [
            'settings' => array_merge($branding, $contact, $social),
            'clinic' => Clinic::query()->where('is_active', true)->orderBy('id')->first(),
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'serviceCategories' => ServiceCategory::query()
                ->where('is_active', true)
                ->whereHas('services', fn ($query) => $query->where('is_active', true))
                ->with([
                    'services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get(),
            'featuredServices' => Service::query()->where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->limit(6)->get(),
            'doctor' => Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(),
            'stats' => Stat::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'technologies' => Technology::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get(),
        ]);
    }
}
