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
                'description' => 'Procedimientos orientados a armonización, reposición de volumen, bioestimulación y rejuvenecimiento facial con criterio médico.',
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
                'description' => 'Protocolos corporales complementarios orientados a soporte metabólico, recuperación y bienestar integral.',
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
                'subcategory' => 'Rellenos faciales',
                'short' => 'Relleno facial para restaurar volumen, hidratar tejidos y mejorar la definición del rostro.',
                'description' => '<p>Procedimiento con ácido hialurónico orientado a reposición de volumen, soporte estructural y armonización facial en zonas seleccionadas según valoración médica.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 1,
            ],
            [
                'category' => $facial->id,
                'name' => 'Radiesse (hidroxiapatita de calcio)',
                'subcategory' => 'Rellenos faciales',
                'short' => 'Relleno bioestimulador para soporte facial, firmeza y mejora progresiva de la piel.',
                'description' => '<p>Tratamiento con hidroxiapatita de calcio indicado para aportar estructura, estimular colágeno y mejorar la calidad cutánea de forma progresiva.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 2,
                'slug' => 'radiesse',
            ],
            [
                'category' => $facial->id,
                'name' => 'Grasa autóloga (reposición grasa facial)',
                'subcategory' => 'Rellenos faciales',
                'short' => 'Reposición biológica de volumen para recuperar contornos y suavidad facial.',
                'description' => '<p>Procedimiento de reposición grasa facial enfocado en restaurar volúmenes perdidos y mejorar transiciones del rostro con un resultado natural.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 3,
                'slug' => 'grasa-autologa',
            ],
            [
                'category' => $facial->id,
                'name' => 'Sculptra (ácido poliláctico)',
                'subcategory' => 'Bioestimuladores de colágeno',
                'short' => 'Bioestimulador para firmeza, soporte profundo y mejora progresiva de la textura.',
                'description' => '<p>Bioestimulador de colágeno con ácido poliláctico indicado para recuperar soporte, firmeza y densidad cutánea con evolución progresiva.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 4,
                'slug' => 'sculptra',
            ],
            [
                'category' => $facial->id,
                'name' => 'HarmonyCa (Radiesse + ác. hialurónico)',
                'subcategory' => 'Bioestimuladores de colágeno',
                'short' => 'Combinación avanzada para soporte, lifting sutil y armonización del contorno.',
                'description' => '<p>Procedimiento combinado con bioestimulación y reposición de volumen para lograr definición, soporte estructural y rejuvenecimiento armónico.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 5,
                'slug' => 'harmonyca',
            ],
            [
                'category' => $facial->id,
                'name' => 'Ellansé',
                'subcategory' => 'Bioestimuladores de colágeno',
                'short' => 'Bioestimulador con efecto estructural y resultados progresivos de larga duración.',
                'description' => '<p>Tratamiento con policaprolactona orientado a estimular colágeno, reforzar estructura facial y sostener resultados en el tiempo.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 6,
                'slug' => 'ellanse',
            ],
            [
                'category' => $facial->id,
                'name' => 'Rinomodelación',
                'subcategory' => 'Armonización facial con ácido hialurónico',
                'short' => 'Perfilado nasal sin cirugía para mejorar proporción, ángulos y armonía del perfil.',
                'description' => '<p>Procedimiento mínimamente invasivo orientado a corregir irregularidades del dorso, proyectar zonas estratégicas y equilibrar el perfil facial.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 7,
            ],
            [
                'category' => $facial->id,
                'name' => 'Hidratación de Labios',
                'subcategory' => 'Armonización facial con ácido hialurónico',
                'short' => 'Mejora hidratación, textura y frescura del labio manteniendo naturalidad.',
                'description' => '<p>Tratamiento de revitalización labial orientado a recuperar hidratación, definición suave y mejor calidad del tejido sin sobrecorrección.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 8,
            ],
            [
                'category' => $facial->id,
                'name' => 'Mentón Proyección',
                'subcategory' => 'Armonización facial con ácido hialurónico',
                'short' => 'Proyección estratégica del mentón para mejorar balance, perfil y estructura facial.',
                'description' => '<p>Procedimiento con ácido hialurónico orientado a optimizar la proporción del tercio inferior y reforzar la lectura estética del perfil.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 9,
            ],
            [
                'category' => $facial->id,
                'name' => 'Relleno de Pómulos',
                'subcategory' => 'Armonización facial con ácido hialurónico',
                'short' => 'Reposición de volumen malar para soporte, elevación visual y rejuvenecimiento.',
                'description' => '<p>Tratamiento indicado para recuperar estructura del tercio medio, mejorar la transición facial y aportar un efecto de mayor soporte.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 10,
            ],
            [
                'category' => $facial->id,
                'name' => 'Peptonas',
                'subcategory' => 'Revitalización facial',
                'short' => 'Revitalización con complejos bioactivos para mejorar luminosidad, textura y vitalidad.',
                'description' => '<p>Protocolo de revitalización facial enfocado en pieles opacas, deshidratadas o con pérdida de uniformidad en textura y tono.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 11,
            ],
            [
                'category' => $facial->id,
                'name' => 'PDNR de Salmón',
                'subcategory' => 'Revitalización facial',
                'short' => 'Regeneración cutánea con polinucleótidos para reparación, hidratación y soporte.',
                'description' => '<p>Tratamiento regenerativo orientado a estimular reparación tisular, mejorar hidratación profunda y favorecer recuperación de la calidad de piel.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 12,
            ],
            [
                'category' => $facial->id,
                'name' => 'NCTF',
                'subcategory' => 'Revitalización facial',
                'short' => 'Complejo revitalizante para hidratación profunda, glow y mejor calidad cutánea.',
                'description' => '<p>Protocolo de revitalización diseñado para aportar hidratación profunda, mejorar textura y potenciar luminosidad sostenida.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 13,
            ],
            [
                'category' => $facial->id,
                'name' => 'Perfilamiento Mandibular',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Definición del borde mandibular para mejorar contorno, estructura y perfil facial.',
                'description' => '<p>Procedimiento mínimamente invasivo orientado a reforzar la línea mandibular y aportar una lectura facial más estructurada y armónica.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 14,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lipopapada',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Mejora del contorno submentoniano y definición del ángulo cervicomentoniano.',
                'description' => '<p>Procedimiento dirigido a reducir acúmulo graso localizado bajo el mentón y optimizar la definición del cuello y perfil inferior.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 15,
            ],
            [
                'category' => $facial->id,
                'name' => 'Blefaroplastia',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Rejuvenecimiento de párpados para una mirada más fresca, descansada y funcional.',
                'description' => '<p>Procedimiento periocular orientado a corregir exceso de piel o bolsas y recuperar una mirada más limpia, descansada y armónica.</p>',
                'featured' => true,
                'premium' => true,
                'sort' => 16,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lifting de Cejas',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Elevación sutil de cejas para abrir la mirada y suavizar la expresión.',
                'description' => '<p>Procedimiento enfocado en reposicionar tejidos del tercio superior para aportar mayor apertura visual y expresión más descansada.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 17,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lifting Labio Superior',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Mejora la proporción del labio superior y la estética de la zona perioral.',
                'description' => '<p>Procedimiento orientado a optimizar la exposición del bermellón, mejorar la proporción labial y rejuvenecer el área perioral.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 18,
            ],
            [
                'category' => $facial->id,
                'name' => 'Alectomía',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Refinamiento de la base alar para mejorar proporción y lectura nasal.',
                'description' => '<p>Procedimiento orientado a corregir la amplitud de la base nasal y mejorar la armonía general de la nariz con el rostro.</p>',
                'featured' => false,
                'premium' => true,
                'sort' => 19,
            ],
            [
                'category' => $facial->id,
                'name' => 'Lobuloplastia',
                'subcategory' => 'Procedimientos mínimamente invasivos',
                'short' => 'Reparación del lóbulo auricular para restaurar forma, cierre y estética.',
                'description' => '<p>Procedimiento para reparar o remodelar el lóbulo de la oreja en casos de desgarro, elongación o corrección estética.</p>',
                'featured' => false,
                'premium' => false,
                'sort' => 20,
            ],
            [
                'category' => $corporal->id,
                'name' => 'Sueroterapia',
                'subcategory' => 'Sueroterapia',
                'short' => 'Terapia complementaria para soporte metabólico, energía, hidratación y bienestar integral.',
                'description' => '<p>Protocolo de sueroterapia orientado a apoyo metabólico, hidratación, recuperación y acompañamiento de tratamientos integrales de bienestar.</p>',
                'featured' => true,
                'premium' => false,
                'sort' => 1,
            ],
        ] as $service) {
            Service::query()->updateOrCreate(
                ['clinic_id' => $clinic->id, 'slug' => $service['slug'] ?? Str::slug($service['name'])],
                [
                    'clinic_id' => $clinic->id,
                    'service_category_id' => $service['category'],
                    'name' => $service['name'],
                    'slug' => $service['slug'] ?? Str::slug($service['name']),
                    'short_description' => $service['short'],
                    'description' => $service['description'],
                    'meta' => ['subcategory' => $service['subcategory']],
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
