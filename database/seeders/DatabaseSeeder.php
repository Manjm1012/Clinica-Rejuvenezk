<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Lead;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\Stat;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $clinic = Clinic::query()->updateOrCreate([
            'slug' => 'clinica-rejuvenezk',
        ], [
            'name' => 'Clinica Rejuvenezk',
            'domain' => null,
            'timezone' => 'America/Bogota',
            'locale' => 'es',
            'is_active' => true,
        ]);

        User::query()->updateOrCreate([
            'email' => 'admin@rejuvenezk.test',
        ], [
            'clinic_id' => $clinic->id,
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
        ]);

        foreach ([
            ['group' => 'branding', 'key' => 'clinic_name', 'value' => 'Clínica Rejuvenezk', 'type' => 'string', 'label' => 'Nombre clínica'],
            ['group' => 'branding', 'key' => 'hero_kicker', 'value' => 'Medicina estética avanzada', 'type' => 'string', 'label' => 'Etiqueta hero'],
            ['group' => 'branding', 'key' => 'hero_title', 'value' => 'Medicina estética y cirugía con enfoque premium', 'type' => 'string', 'label' => 'Título principal'],
            ['group' => 'branding', 'key' => 'hero_subtitle', 'value' => 'Una plataforma comercializable para clínicas estéticas con sitio web, CRM y WhatsApp integrados.', 'type' => 'text', 'label' => 'Subtítulo principal'],
            ['group' => 'branding', 'key' => 'hero_primary_cta', 'value' => 'Reservar valoración', 'type' => 'string', 'label' => 'CTA principal hero'],
            ['group' => 'branding', 'key' => 'hero_secondary_cta', 'value' => 'Ver tratamientos', 'type' => 'string', 'label' => 'CTA secundario hero'],
            ['group' => 'branding', 'key' => 'hero_card_kicker', 'value' => 'Primera cita', 'type' => 'string', 'label' => 'Etiqueta tarjeta hero'],
            ['group' => 'branding', 'key' => 'hero_card_title', 'value' => 'Diagnóstico inteligente de piel', 'type' => 'string', 'label' => 'Título tarjeta hero'],
            ['group' => 'branding', 'key' => 'hero_card_text', 'value' => 'Análisis de calidad cutánea y plan estético por etapas para resultados armónicos y progresivos.', 'type' => 'text', 'label' => 'Texto tarjeta hero'],
            ['group' => 'branding', 'key' => 'hero_stack_primary_label', 'value' => 'Protocolo estrella', 'type' => 'string', 'label' => 'Label bloque hero 1'],
            ['group' => 'branding', 'key' => 'hero_stack_secondary_label', 'value' => 'Especialista', 'type' => 'string', 'label' => 'Label bloque hero 2'],
            ['group' => 'branding', 'key' => 'services_kicker', 'value' => 'Nuestros tratamientos', 'type' => 'string', 'label' => 'Etiqueta servicios'],
            ['group' => 'branding', 'key' => 'services_title', 'value' => 'Tecnología, precisión y naturalidad', 'type' => 'string', 'label' => 'Título servicios'],
            ['group' => 'branding', 'key' => 'testimonials_kicker', 'value' => 'Testimonios', 'type' => 'string', 'label' => 'Etiqueta testimonios'],
            ['group' => 'branding', 'key' => 'testimonials_title', 'value' => 'Pacientes que ya viven su cambio', 'type' => 'string', 'label' => 'Título testimonios'],
            ['group' => 'branding', 'key' => 'social_kicker', 'value' => 'Comunidad Rejuvenezk', 'type' => 'string', 'label' => 'Etiqueta redes'],
            ['group' => 'branding', 'key' => 'social_title', 'value' => 'Conecta con nuestras redes y canales', 'type' => 'string', 'label' => 'Título redes'],
            ['group' => 'branding', 'key' => 'social_lead', 'value' => 'Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.', 'type' => 'text', 'label' => 'Texto redes'],
            ['group' => 'branding', 'key' => 'cta_kicker', 'value' => 'Agenda tu cita', 'type' => 'string', 'label' => 'Etiqueta CTA final'],
            ['group' => 'branding', 'key' => 'cta_title', 'value' => 'Empieza hoy tu protocolo personalizado.', 'type' => 'string', 'label' => 'Título CTA final'],
            ['group' => 'branding', 'key' => 'cta_body', 'value' => 'Atención presencial y seguimiento digital para una experiencia comercial ordenada.', 'type' => 'text', 'label' => 'Texto CTA final'],
            ['group' => 'branding', 'key' => 'topbar_cta_label', 'value' => 'Agenda ahora', 'type' => 'string', 'label' => 'CTA topbar'],
            ['group' => 'branding', 'key' => 'cta_whatsapp_label', 'value' => 'WhatsApp', 'type' => 'string', 'label' => 'Texto botón WhatsApp'],
            ['group' => 'branding', 'key' => 'cta_email_label', 'value' => 'Solicitar información', 'type' => 'string', 'label' => 'Texto botón correo'],
            ['group' => 'contact', 'key' => 'whatsapp_number', 'value' => '+573223324156', 'type' => 'string', 'label' => 'WhatsApp'],
            ['group' => 'contact', 'key' => 'phone', 'value' => '+57 322 332 4156', 'type' => 'string', 'label' => 'Teléfono'],
            ['group' => 'contact', 'key' => 'email', 'value' => 'hola@rejuvenezk.com', 'type' => 'string', 'label' => 'Email'],
            ['group' => 'contact', 'key' => 'address', 'value' => 'Bogotá, Colombia', 'type' => 'string', 'label' => 'Dirección'],
            ['group' => 'social', 'key' => 'instagram_url', 'value' => 'https://instagram.com', 'type' => 'string', 'label' => 'Instagram'],
            ['group' => 'social', 'key' => 'facebook_url', 'value' => 'https://facebook.com', 'type' => 'string', 'label' => 'Facebook'],
            ['group' => 'social', 'key' => 'tiktok_url', 'value' => 'https://tiktok.com', 'type' => 'string', 'label' => 'TikTok'],
            ['group' => 'social', 'key' => 'youtube_url', 'value' => 'https://youtube.com', 'type' => 'string', 'label' => 'YouTube'],
            ['group' => 'tayrai', 'key' => 'enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Activar integración'],
            ['group' => 'tayrai', 'key' => 'api_key', 'value' => '', 'type' => 'string', 'label' => 'API Key'],
            ['group' => 'tayrai', 'key' => 'webhook_secret', 'value' => '', 'type' => 'string', 'label' => 'Webhook Secret'],
        ] as $setting) {
            SiteSetting::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'group' => $setting['group'], 'key' => $setting['key']],
                ['clinic_id' => $clinic->id] + $setting,
            );
        }

        $facial = ServiceCategory::query()->updateOrCreate(
            ['clinic_id' => $clinic->id, 'slug' => 'cirugias-faciales'],
            ['clinic_id' => $clinic->id, 'name' => 'Cirugías Faciales', 'description' => 'Procedimientos faciales premium', 'icon' => 'heroicon-o-face-smile']
        );

        $corporal = ServiceCategory::query()->updateOrCreate(
            ['clinic_id' => $clinic->id, 'slug' => 'cirugias-corporales'],
            ['clinic_id' => $clinic->id, 'name' => 'Cirugías Corporales', 'description' => 'Contorno corporal y definición', 'icon' => 'heroicon-o-sparkles']
        );

        foreach ([
            ['category' => $corporal->id, 'name' => 'Lipoescultura', 'short' => 'Definición corporal avanzada con técnica personalizada.', 'featured' => true, 'premium' => true],
            ['category' => $corporal->id, 'name' => 'Abdominoplastia', 'short' => 'Remodelación abdominal con enfoque funcional y estético.', 'featured' => true, 'premium' => true],
            ['category' => $facial->id, 'name' => 'Rinoplastia', 'short' => 'Armonización nasal estética y funcional.', 'featured' => true, 'premium' => true],
        ] as $service) {
            Service::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'slug' => Str::slug($service['name'])],
                [
                    'clinic_id' => $clinic->id,
                    'service_category_id' => $service['category'],
                    'name' => $service['name'],
                    'slug' => Str::slug($service['name']),
                    'short_description' => $service['short'],
                    'description' => '<p>Contenido editable desde Filament para comercializar este sitio a múltiples clínicas.</p>',
                    'is_featured' => $service['featured'],
                    'is_premium' => $service['premium'],
                    'is_active' => true,
                ],
            );
        }

        Doctor::query()->updateOrCreate(
            ['clinic_id' => $clinic->id, 'name' => 'Dr. Rejuvenezk'],
            [
                'clinic_id' => $clinic->id,
                'specialty' => 'Cirugía Plástica y Medicina Estética',
                'subtitle' => 'Especialista certificado con enfoque premium',
                'bio' => 'Perfil profesional editable para cada cliente desde el panel administrativo.',
                'university' => 'Universidad de referencia',
                'is_active' => true,
            ],
        );

        foreach ([
            ['label' => 'Años de Experiencia', 'value' => '+15'],
            ['label' => 'Procedimientos Realizados', 'value' => '+1800'],
            ['label' => 'Pacientes Satisfechos', 'value' => '98%'],
        ] as $stat) {
            Stat::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'label' => $stat['label']],
                ['clinic_id' => $clinic->id] + $stat + ['is_active' => true]
            );
        }

        foreach ([
            ['name' => 'TENSAMAX', 'description' => 'Tecnología complementaria para retracción y moldeado.'],
            ['name' => 'Láser CO2', 'description' => 'Soporte para cicatrices, acné y estrías.'],
        ] as $tech) {
            Technology::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'name' => $tech['name']],
                ['clinic_id' => $clinic->id] + $tech + ['is_active' => true]
            );
        }

        Testimonial::query()->updateOrCreate(
            ['clinic_id' => $clinic->id, 'patient_name' => 'Paciente Demo'],
            [
                'clinic_id' => $clinic->id,
                'content' => 'Excelente atención, seguimiento y resultados. Este bloque también es editable desde Filament.',
                'rating' => 5,
                'source' => 'google',
                'is_featured' => true,
                'is_active' => true,
            ],
        );

        Lead::query()->firstOrCreate(
            ['clinic_id' => $clinic->id, 'phone' => '3000000000'],
            ['clinic_id' => $clinic->id, 'name' => 'Lead Demo', 'email' => 'lead@example.com', 'status' => 'new', 'source' => 'website']
        );
    }
}
