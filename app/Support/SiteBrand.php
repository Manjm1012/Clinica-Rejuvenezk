<?php

namespace App\Support;

use App\Models\Doctor;

/**
 * Datos de marca y contacto que usa el layout público en todas las páginas.
 */
class SiteBrand
{
    public static function for(array $settings): array
    {
        $clinic = app()->bound(CurrentClinic::class) ? app(CurrentClinic::class)->clinic() : null;
        $clinicName = $settings['clinic_name'] ?? $clinic?->name ?? 'Dr. Kevin Rejuvenezk';
        $doctor = Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(['id', 'name', 'specialty', 'photo_path']);

        $whatsappRaw = trim((string) ($settings['whatsapp_url'] ?? ''));
        $whatsappDigits = preg_replace('/\D+/', '', (string) ($settings['whatsapp_number'] ?? ''));
        $hasWhatsapp = $whatsappRaw !== '' || $whatsappDigits !== '';
        $whatsappUrl = $whatsappRaw !== '' ? $whatsappRaw : ($whatsappDigits !== '' ? 'https://wa.me/'.$whatsappDigits : route('home').'#contacto');
        $bookingText = $settings['whatsapp_booking_text'] ?? 'Hola, quiero agendar una valoración';

        $social = collect([
            ['label' => 'Instagram', 'icon' => 'instagram', 'url' => $settings['instagram_url'] ?? null],
            ['label' => 'Facebook', 'icon' => 'facebook', 'url' => $settings['facebook_url'] ?? null],
            ['label' => 'TikTok', 'icon' => 'tiktok', 'url' => $settings['tiktok_url'] ?? null],
            ['label' => 'YouTube', 'icon' => 'youtube', 'url' => $settings['youtube_url'] ?? null],
        ])->filter(fn (array $item) => filled($item['url']))->values()->all();

        return [
            'clinicName' => $clinicName,
            'initials' => Media::initials($clinicName),
            'logoUrl' => Media::url($clinic?->logo_path),
            'doctor' => $doctor,
            'doctorPhotoUrl' => Media::url($doctor?->photo_path),
            // Línea secundaria: el médico; si se llama igual que la marca, la especialidad (no repetir el nombre).
            'tagline' => $doctor?->name && mb_strtolower($doctor->name) !== mb_strtolower($clinicName)
                ? $doctor->name
                : ($doctor?->specialty ?: 'Medicina estética'),
            'hasWhatsapp' => $hasWhatsapp,
            'whatsappUrl' => $whatsappUrl,
            'bookingUrl' => $hasWhatsapp
                ? $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').'text='.rawurlencode($bookingText)
                : route('home').'#formulario',
            'social' => $social,
            'phone' => $settings['phone'] ?? '',
            'email' => $settings['email'] ?? '',
            'address' => $settings['address'] ?? '',
        ];
    }
}
