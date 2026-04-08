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
            ['clinic_id' => $clinic->id, 'slug' => 'facial'],
            [
                'clinic_id' => $clinic->id,
                'name' => 'Facial',
                'description' => 'Procedimientos faciales para armonización, bioestimulación, revitalización y cirugía menor.',
                'icon' => 'heroicon-o-face-smile',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $corporal = ServiceCategory::query()->updateOrCreate(
            ['clinic_id' => $clinic->id, 'slug' => 'corporal'],
            [
                'clinic_id' => $clinic->id,
                'name' => 'Corporal',
                'description' => 'Protocolos corporales complementarios y terapias de soporte para bienestar integral.',
                'icon' => 'heroicon-o-sparkles',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        ServiceCategory::query()
            ->where('clinic_id', $clinic->id)
            ->whereIn('slug', ['cirugias-faciales', 'cirugias-corporales'])
            ->update(['is_active' => false]);

        Service::query()
            ->where('clinic_id', $clinic->id)
            ->whereIn('slug', ['lipoescultura', 'abdominoplastia', 'rinoplastia'])
            ->update(['is_active' => false]);

        foreach ([
            [
                'category' => $facial->id,
                'name' => 'Ácido Hialurónico',
                'short' => 'Rellenos faciales para restaurar volumen, hidratación y definición del rostro.',
                'description' => '<p>Tratamiento de rellenos faciales con ácido hialurónico para soporte, proyección y armonización de distintas zonas faciales.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 1,
            ],
            [
                'category' => $facial->id,
                'name' => 'Radiesse',
                'short' => 'Relleno facial con hidroxiapatita de calcio para soporte estructural y bioestimulación.',
                'description' => '<p>Procedimiento de relleno facial con hidroxiapatita de calcio orientado a soporte y mejora progresiva de la calidad de la piel.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 2,
            ],
            [
                'category' => $facial->id,
                'name' => 'Grasa Autóloga',
                'short' => 'Reposición de grasa facial para restaurar volumen de forma biológica.',
                'description' => '<p>Reposición de grasa facial con enfoque en recuperación de volúmenes y contornos de manera natural.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 3,
            ],
            [
                'category' => $facial->id,
                'name' => 'Sculptra',
                'short' => 'Bioestimulador de colágeno con ácido poliláctico para firmeza y soporte.',
                'description' => '<p>Bioestimulador de colágeno con ácido poliláctico para estimular firmeza, estructura y calidad cutánea.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 4,
            ],
            [
                'category' => $facial->id,
                'name' => 'Harmonyca',
                'short' => 'Combinación de Radiesse y ácido hialurónico para lifting y definición.',
                'description' => '<p>Tratamiento combinado con soporte estructural y efecto de armonización facial avanzada.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 5,
            ],
            [
                'category' => $facial->id,
                'name' => 'Ellansé',
                'short' => 'Bioestimulador con policaprolactona para volumen progresivo y longevidad.',
                'description' => '<p>Bioestimulador diseñado para estimular colágeno y mejorar la estructura del rostro con resultados progresivos.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 6,
            ],
            [
                'category' => $facial->id,
                'name' => 'Rinomodelación',
                'short' => 'Armonización facial con ácido hialurónico para perfilar la nariz sin cirugía.',
                'description' => '<p>Procedimiento mínimamente invasivo para mejorar el perfil nasal y la proporción facial.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 7,
            ],
            [
                'category' => $facial->id,
                'name' => 'Hidratación de Labios',
                'short' => 'Mejora de hidratación, textura y frescura labial con ácido hialurónico.',
                'description' => '<p>Protocolo enfocado en revitalizar labios, mejorar definición y conservar naturalidad.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 8,
            ],
            [
                'category' => $facial->id,
                'name' => 'Mentón Proyección',
                'short' => 'Proyección de mentón para balancear el perfil y la armonización facial.',
                'description' => '<p>Tratamiento con ácido hialurónico para mejorar soporte y proporción del tercio inferior.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 9,
            ],
            [
                'category' => $facial->id,
                'name' => 'Relleno de Pómulos',
                'short' => 'Reposición de volumen malar para rejuvenecimiento y soporte medio facial.',
                'description' => '<p>Procedimiento para recuperar estructura, proyección y transición armónica del rostro.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 10,
            ],
            [
                'category' => $facial->id,
                'name' => 'Peptonas',
                'short' => 'Revitalización facial con complejos bioactivos para luminosidad y textura.',
                'description' => '<p>Tratamiento de revitalización facial orientado a piel opaca, hidratación y calidad cutánea.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 11,
            ],
            [
                'category' => $facial->id,
                'name' => 'PDNR de Salmón',
                'short' => 'Revitalización con polinucleótidos para reparación, hidratación y regeneración.',
                'description' => '<p>Protocolo regenerativo para recuperación de calidad de piel y soporte tisular.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 12,
            ],
            [
                'category' => $facial->id,
                'name' => 'NCTF',
                'short' => 'Complejo revitalizante facial para hidratación profunda y efecto glow.',
                'description' => '<p>Revitalización facial orientada a textura, hidratación y luminosidad sostenida.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 13,
            ],
            [
                'category' => $facial->id,
                'name' => 'Perfilamiento Mandibular',
                'short' => 'Definición mandibular mínimamente invasiva para contorno y estructura.',
                'description' => '<p>Tratamiento orientado a mejorar el borde mandibular y reforzar la armonización del perfil.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 14,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lipopapada',
                'short' => 'Procedimiento mínimamente invasivo para mejorar el contorno submentoniano.',
                'description' => '<p>Tratamiento diseñado para reducir acúmulo graso localizado y definir el ángulo cervicomentoniano.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 15,
            ],
            [
                'category' => $facial->id,
                'name' => 'Blefaroplastia',
                'short' => 'Corrección mínimamente invasiva de párpados para mirada más fresca y funcional.',
                'description' => '<p>Procedimiento para rejuvenecimiento periocular con enfoque funcional y estético.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 16,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lifting de Cejas',
                'short' => 'Elevación sutil de cejas para una mirada más abierta y armónica.',
                'description' => '<p>Procedimiento orientado a reposicionar tejidos y mejorar expresión del tercio superior.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 17,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lifting Labio Superior',
                'short' => 'Mejora de proporción del labio superior con enfoque funcional y estético.',
                'description' => '<p>Procedimiento para optimizar la exposición del bermellón y la armonía perioral.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 18,
            ],
            [
                'category' => $facial->id,
                'name' => 'Alectomía',
                'short' => 'Corrección de base alar para refinamiento y proporción nasal.',
                'description' => '<p>Procedimiento mínimamente invasivo orientado a mejorar la proporción de la base nasal.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 19,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lobuloplastia',
                'short' => 'Corrección de lóbulo auricular para restaurar forma y funcionalidad.',
                'description' => '<p>Procedimiento para reparar o remodelar el lóbulo de la oreja con cierre estético.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 20,
            ],
            [
                'category' => $corporal->id,
                'name' => 'Sueroterapia',
                'short' => 'Terapia corporal complementaria para soporte metabólico, energía y bienestar integral.',
                'description' => '<p>Protocolo corporal de soporte con sueroterapia orientado a recuperación, energía y acompañamiento de tratamientos integrales.</p>',
                'featured' => true,
                'premium' => false,
                'sort' => 1,
            ],
        ] as $service) {
            Service::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'slug' => Str::slug($service['name'])],
                [
                    'clinic_id' => $clinic->id,
                    'service_category_id' => $service['category'],
                    'name' => $service['name'],
                    'slug' => Str::slug($service['name']),
                    'short_description' => $service['short'],
                    'description' => $service['description'],
                    'is_featured' => $service['featured'],
                    'is_premium' => $service['premium'],
                    'is_active' => true,
                    'sort_order' => $service['sort'],
                ],
            );
        }

        $doctor = Doctor::query()->where('clinic_id', $clinic->id)->orderBy('id')->first() ?? new Doctor();
        $doctor->fill([
            'clinic_id' => $clinic->id,
            'name' => 'Dr Kevin-Rejuvenezk',
            'specialty' => 'Medicina Estética Facial',
            'subtitle' => 'Armonización facial y procedimientos mínimamente invasivos',
            'bio' => 'Especialista enfocado en armonización facial, bioestimulación y procedimientos mínimamente invasivos con criterio médico.',
            'university' => 'Formación médica estética especializada',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $doctor->save();

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
