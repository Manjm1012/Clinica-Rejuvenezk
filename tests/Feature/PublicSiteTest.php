<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Sitio público: portada por secciones, páginas nuevas, SEO y contenido honesto.
 */
class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
    }

    private function make(string $class, array $attributes)
    {
        $model = new $class($attributes);
        $model->clinic_id = $this->clinic->id;
        $model->save();

        return $model;
    }

    public function test_la_portada_responde_con_sus_secciones_y_seo(): void
    {
        $this->make(Service::class, ['name' => 'Toxina botulínica', 'slug' => 'toxina', 'is_active' => true, 'is_featured' => true]);
        $this->make(Doctor::class, ['name' => 'Dr. Kevin', 'specialty' => 'Medicina estética', 'is_active' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="servicios"', false)
            ->assertSee('id="contacto"', false)
            ->assertSee('id="formulario"', false)
            ->assertSee('Toxina botulínica')
            ->assertSee('Dr. Kevin')
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('"@type":"MedicalClinic"', false);
    }

    public function test_sin_testimonios_reales_no_se_inventan_opiniones(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('Paciente verificado')
            ->assertDontSee('4.9/5')
            ->assertDontSee('Me encantó porque respetaron mis facciones');
    }

    public function test_la_valoracion_se_calcula_con_testimonios_reales(): void
    {
        $this->make(Testimonial::class, ['patient_name' => 'Laura', 'content' => 'Excelente atención', 'rating' => 5, 'is_active' => true]);
        $this->make(Testimonial::class, ['patient_name' => 'Marta', 'content' => 'Muy profesionales', 'rating' => 4, 'is_active' => true]);

        $this->get('/')->assertOk()->assertSee('Valoración 4,5/5')->assertSee('Excelente atención')->assertSee('2 opiniones');
    }

    public function test_el_formulario_conserva_las_utm_de_la_campana(): void
    {
        $this->get('/?utm_source=instagram&utm_campaign=noviembre')->assertOk()
            ->assertSee('name="utm_source" value="instagram"', false)
            ->assertSee('name="utm_campaign" value="noviembre"', false);
    }

    public function test_paginas_nuevas_responden(): void
    {
        $doctor = $this->make(Doctor::class, ['name' => 'Dra. Ruiz', 'specialty' => 'Dermatología estética', 'is_active' => true]);
        $this->make(Doctor::class, ['name' => 'Dr. Inactivo', 'is_active' => false]);
        $this->make(Faq::class, ['question' => '¿Duele la aplicación?', 'answer' => 'Se usa anestesia tópica.', 'is_active' => true]);
        $this->make(Faq::class, ['question' => 'Pregunta oculta', 'answer' => 'x', 'is_active' => false]);

        $this->get(route('team'))->assertOk()->assertSee('Dra. Ruiz')->assertDontSee('Dr. Inactivo')->assertSee('"@type":"Physician"', false);
        $this->get(route('faq'))->assertOk()->assertSee('¿Duele la aplicación?')->assertDontSee('Pregunta oculta')->assertSee('"@type":"FAQPage"', false);
        $this->get(route('results'))->assertOk()->assertSee('Cambios reales');
        $this->get(route('about'))->assertOk()->assertSee('Misión');
        $this->get(route('services.index'))->assertOk();
    }

    public function test_sitemap_incluye_servicios_activos(): void
    {
        $this->make(Service::class, ['name' => 'Peeling', 'slug' => 'peeling', 'is_active' => true]);
        $this->make(Service::class, ['name' => 'Oculto', 'slug' => 'oculto', 'is_active' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('services.show', 'peeling'), false)
            ->assertDontSee('/procedimientos/oculto', false);
    }

    public function test_la_ficha_del_servicio_no_muestra_textos_internos(): void
    {
        $this->make(Service::class, ['name' => 'Peeling químico', 'slug' => 'peeling', 'short_description' => 'Renueva la piel', 'is_active' => true]);

        $this->get('/procedimientos/peeling')->assertOk()
            ->assertDontSee('editables desde Filament')
            ->assertSee('<meta name="description" content="Renueva la piel">', false);
    }

    public function test_los_textos_de_demostracion_no_llegan_al_paciente(): void
    {
        foreach ([
            ['hero_title', 'Medicina estética y cirugía con enfoque premium'],
            ['hero_subtitle', 'Una plataforma comercializable para clínicas estéticas con sitio web, CRM y WhatsApp integrados.'],
        ] as [$key, $value]) {
            $this->make(SiteSetting::class, ['group' => 'branding', 'key' => $key, 'value' => $value, 'type' => 'string']);
        }
        Cache::flush();

        $this->get('/')->assertOk()
            ->assertSee('Medicina estética y cirugía con enfoque médico')
            ->assertDontSee('enfoque premium')
            ->assertDontSee('comercializable');
    }
}
