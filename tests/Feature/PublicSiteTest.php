<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Stat;
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
            // El formulario vive en /contacto: la portada solo trae un resumen con el botón.
            ->assertDontSee('id="formulario"', false)
            ->assertSee('href="'.route('contact').'"', false)
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

    public function test_con_pocas_resenas_se_muestran_las_opiniones_pero_no_el_promedio(): void
    {
        $this->make(Testimonial::class, ['patient_name' => 'Laura', 'content' => 'Excelente atención', 'rating' => 5, 'is_active' => true, 'source_url' => 'https://g.page/r/1']);
        $this->make(Testimonial::class, ['patient_name' => 'Marta', 'content' => 'Muy profesionales', 'rating' => 4, 'is_active' => true]);

        foreach (['/', route('results')] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Excelente atención')
                ->assertDontSee('testimonials-pill', false)
                ->assertDontSee('/5</strong>', false);
        }
    }

    public function test_las_resenas_sin_enlace_de_verificacion_no_se_muestran(): void
    {
        // La reseña de ejemplo que sembraban versiones antiguas del seeder.
        $this->make(Testimonial::class, [
            'patient_name' => 'Paciente Demo', 'rating' => 5, 'is_active' => true,
            'content' => 'Excelente atención, seguimiento y resultados. Este bloque también es editable desde Filament.',
        ]);
        $this->make(Testimonial::class, ['patient_name' => 'Rosa', 'content' => 'Opinión real verificada', 'rating' => 5, 'is_active' => true, 'source_url' => 'https://g.page/r/rosa']);
        $this->make(Testimonial::class, ['patient_name' => 'Oculta', 'content' => 'Opinión desactivada', 'rating' => 5, 'is_active' => false, 'source_url' => 'https://g.page/r/x']);

        foreach (['/', route('results')] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Opinión real verificada')
                ->assertSee('https://g.page/r/rosa', false)
                ->assertDontSee('editable desde Filament')
                ->assertDontSee('Paciente Demo')
                ->assertDontSee('Experiencia en')
                ->assertDontSee('Opinión desactivada');
        }

        $this->getJson('/api/home')->assertOk()->assertDontSee('editable desde Filament');
    }

    public function test_las_cifras_de_la_portada_salen_de_los_ajustes_y_vacias_no_se_muestran(): void
    {
        // Las cifras que sembraba el seeder en la tabla stats ya no se publican.
        $this->make(Stat::class, ['label' => 'Años de Experiencia', 'value' => '+15', 'is_active' => true]);
        $this->make(Stat::class, ['label' => 'Pacientes Satisfechos', 'value' => '98%', 'is_active' => true]);

        $this->get('/')->assertOk()
            ->assertDontSee('+15')
            ->assertDontSee('98%')
            ->assertDontSee('Años de Experiencia');

        foreach ([['stat_1_value', '+10'], ['stat_1_label', 'años de experiencia'], ['stat_2_value', '+500'], ['stat_3_label', 'Sin valor']] as [$key, $value]) {
            $this->make(SiteSetting::class, ['group' => 'branding', 'key' => $key, 'value' => $value, 'type' => 'string']);
        }
        Cache::flush();

        $this->get('/')->assertOk()
            ->assertSee('+10')
            ->assertSee('años de experiencia')
            ->assertDontSee('+500')      // cifra sin etiqueta
            ->assertDontSee('Sin valor'); // etiqueta sin cifra
    }

    public function test_el_promedio_aparece_desde_diez_resenas_verificables(): void
    {
        $verificable = fn (int $i, int $rating) => $this->make(Testimonial::class, [
            'patient_name' => "Paciente {$i}", 'content' => "Opinión {$i}", 'rating' => $rating,
            'is_active' => true, 'source_url' => "https://g.page/r/{$i}",
        ]);

        foreach (range(1, 9) as $i) {
            $verificable($i, $i % 2 ? 5 : 4);
        }
        // Sin enlace a la opinión original no cuenta como verificable.
        $this->make(Testimonial::class, ['patient_name' => 'Sin enlace', 'content' => 'x', 'rating' => 5, 'is_active' => true]);

        $this->get('/')->assertOk()->assertDontSee('testimonials-pill', false);

        $verificable(10, 4); // 5 de 5 estrellas y 5 de 4 → 4,5

        foreach (['/', route('results')] as $url) {
            $this->get($url)->assertOk()->assertSee('Valoración 4,5/5')->assertSee('10 opiniones verificables');
        }
    }

    public function test_el_formulario_conserva_las_utm_de_la_campana(): void
    {
        $this->get('/contacto?utm_source=instagram&utm_campaign=noviembre')->assertOk()
            ->assertSee('name="utm_source" value="instagram"', false)
            ->assertSee('name="utm_campaign" value="noviembre"', false);

        // Desde la portada, el botón a /contacto lleva las UTM de la visita.
        $this->get('/?utm_source=instagram&utm_campaign=noviembre')->assertOk()
            ->assertSee(e(route('contact', ['utm_source' => 'instagram', 'utm_campaign' => 'noviembre'])), false);
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

    public function test_las_paginas_publicas_tienen_un_solo_h1_y_encabezado_comun(): void
    {
        $this->make(Testimonial::class, ['patient_name' => 'Laura', 'content' => 'Excelente atención', 'rating' => 5, 'is_active' => true]);

        foreach (['/', route('services.index'), route('results'), route('team'), route('faq'), route('about'), route('contact')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), "Debe haber un solo <h1> en {$url}");
        }

        foreach ([route('services.index'), route('results'), route('team'), route('faq'), route('contact')] as $url) {
            $this->get($url)->assertSee('class="container dk-page-head"', false);
        }

        $results = $this->get(route('results'))->getContent();
        $this->assertSame(1, substr_count($results, '<p class="kicker">Resultados</p>'), 'El kicker "Resultados" no debe repetirse');
    }

    public function test_sitemap_incluye_servicios_activos(): void
    {
        $this->make(Service::class, ['name' => 'Peeling', 'slug' => 'peeling', 'is_active' => true]);
        $this->make(Service::class, ['name' => 'Oculto', 'slug' => 'oculto', 'is_active' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('services.show', 'peeling'), false)
            ->assertSee('<loc>'.route('contact').'</loc>', false)
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

        // Tampoco en la meta descripción de las páginas sin descripción propia.
        foreach ([route('patient.login'), route('patient.register'), route('legal.data-policy')] as $url) {
            $this->get($url)->assertOk()->assertDontSee('comercializable');
        }
    }
}
