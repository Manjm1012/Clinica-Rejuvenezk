<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Ajustes públicos del sitio (branding + contacto + redes) con sus valores por defecto.
 * Lo usan HomeController y las páginas del portal del paciente para el layout.
 */
class PublicSettings
{
    public static function all(): array
    {
        $branding = array_merge([
            'clinic_name' => 'Dr. Kevin Rejuvenezk',
            'hero_kicker' => 'Medicina estética avanzada',
            'hero_title' => 'Cirugía y medicina estética premium',
            'hero_subtitle' => 'Valoración personalizada con médico certificado y resultados naturales, sin perder tu expresión.',
            'hero_primary_cta' => 'Reservar valoración',
            'hero_secondary_cta' => 'Ver tratamientos',
            'hero_card_kicker' => 'Primera cita',
            'hero_card_title' => 'Diagnóstico inteligente de piel',
            'hero_card_text' => 'Análisis de calidad cutánea y plan estético por etapas para resultados armónicos y progresivos.',
            'hero_stack_primary_label' => 'Protocolo estrella',
            'hero_stack_secondary_label' => 'Especialista',
            'services_kicker' => 'Nuestros tratamientos',
            'services_title' => 'Tecnología, precisión y naturalidad',
            'testimonials_kicker' => 'Resultados',
            'testimonials_title' => 'Resultados y testimonios reales',
            'social_kicker' => 'Comunidad Rejuvenezk',
            'social_title' => 'Conecta con nuestras redes y canales',
            'social_lead' => 'Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.',
            'cta_kicker' => 'Agenda tu cita',
            'cta_title' => 'Empieza hoy tu protocolo personalizado.',
            'cta_body' => 'Atención presencial y seguimiento cercano después de cada tratamiento.',
            'topbar_cta_label' => 'Agenda ahora',
            'cta_whatsapp_label' => 'WhatsApp',
            'cta_email_label' => 'Solicitar información',
            'home_banner_1_image' => '',
            'home_banner_2_image' => '',
            'home_banner_3_image' => '',
            // Cifras de la portada (p. ej. "+15" / "años de experiencia"). Vacías = no se muestran.
            'stat_1_value' => '', 'stat_1_label' => '',
            'stat_2_value' => '', 'stat_2_label' => '',
            'stat_3_value' => '', 'stat_3_label' => '',
            'stat_4_value' => '', 'stat_4_label' => '',
        ], SiteSetting::group('branding'));

        $contact = array_merge([
            'whatsapp_url' => '',
            'whatsapp_number' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            // Horarios de atención (texto libre, grupo "contact"). Vacío = no se muestran.
            'business_hours' => '',
        ], SiteSetting::group('contact'));

        $social = array_merge([
            'instagram_url' => '',
            'facebook_url' => '',
            'tiktok_url' => '',
            'youtube_url' => '',
        ], SiteSetting::group('social'));

        return array_merge($branding, $contact, $social);
    }

    /** Enlace de WhatsApp con mensaje prellenado, o null si no hay WhatsApp configurado. */
    public static function whatsappLink(array $settings, string $text): ?string
    {
        $raw = trim((string) ($settings['whatsapp_url'] ?? ''));
        $digits = preg_replace('/\D+/', '', (string) ($settings['whatsapp_number'] ?? ''));
        $base = $raw !== '' ? $raw : ($digits !== '' ? 'https://wa.me/'.$digits : null);

        if (! $base) {
            return null;
        }

        return $base.(str_contains($base, '?') ? '&' : '?').'text='.rawurlencode($text);
    }
}
