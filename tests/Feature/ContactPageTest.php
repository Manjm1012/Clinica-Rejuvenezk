<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Página /contacto y CTA "Agenda ahora" del encabezado.
 */
class ContactPageTest extends TestCase
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

    private function setting(string $group, string $key, string $value): void
    {
        $this->make(SiteSetting::class, ['group' => $group, 'key' => $key, 'value' => $value, 'type' => 'string']);
        Cache::flush();
    }

    public function test_la_pagina_de_contacto_responde_con_un_solo_h1_y_el_formulario(): void
    {
        $html = $this->get('/contacto')->assertOk()
            ->assertSee('class="container dk-page-head"', false)
            ->assertSee('action="'.route('leads.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee(route('legal.data-policy'), false) // aviso de tratamiento de datos
            ->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_el_formulario_de_contacto_guarda_el_lead_y_vuelve_con_mensaje(): void
    {
        $service = $this->make(Service::class, ['name' => 'Peeling', 'slug' => 'peeling', 'is_active' => true]);

        $this->from('/contacto?servicio=peeling')
            ->post('/contacto', [
                'name' => 'Ana Pérez', 'phone' => '3001234567', 'email' => 'ana@example.com',
                'message' => 'Quiero información', 'service_id' => $service->id, 'source' => 'website',
                'utm_source' => 'instagram',
            ])
            ->assertRedirect('/contacto?servicio=peeling')
            ->assertSessionHas('success');

        $lead = Lead::query()->sole();
        $this->assertSame('Ana Pérez', $lead->name);
        $this->assertSame($service->id, $lead->service_id);
        $this->assertSame('Quiero información', $lead->message);
        $this->assertSame('instagram', $lead->utm_source);

        $this->followingRedirects()->from('/contacto')->post('/contacto', ['name' => 'Luis', 'phone' => '3009998877'])
            ->assertOk()->assertSee('Tu solicitud fue enviada correctamente');
    }

    public function test_el_formulario_de_contacto_muestra_los_errores_de_validacion(): void
    {
        $this->from('/contacto')->post('/contacto', ['name' => '', 'phone' => ''])
            ->assertRedirect('/contacto')
            ->assertSessionHasErrors(['name', 'phone']);

        $this->assertSame(0, Lead::query()->count());

        $this->followingRedirects()->from('/contacto')->post('/contacto', ['name' => '', 'phone' => ''])
            ->assertSee('Escribe tu nombre.')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_el_formulario_de_contacto_tiene_limite_anti_spam(): void
    {
        $responses = collect(range(1, 12))->map(fn () => $this->post('/contacto', ['name' => 'Spam', 'phone' => '3000000000'])->status());

        $this->assertContains(429, $responses->all(), 'El throttle "contact-form" debe cortar los envíos repetidos.');
    }

    public function test_el_servicio_llega_preseleccionado_desde_la_tarjeta(): void
    {
        $service = $this->make(Service::class, ['name' => 'Toxina', 'slug' => 'toxina', 'is_active' => true, 'is_featured' => true]);

        $this->get('/contacto?servicio=toxina')->assertOk()
            ->assertSee('<option value="'.$service->id.'" selected>', false);

        $this->get('/contacto?servicio=no-existe')->assertOk()->assertDontSee('selected>', false);

        // La tarjeta de la portada apunta a /contacto con el servicio.
        $this->get('/')->assertOk()->assertSee('href="'.route('contact', ['servicio' => 'toxina']).'"', false);
    }

    public function test_los_canales_salen_de_los_ajustes_y_se_omiten_si_no_existen(): void
    {
        $this->get('/contacto')->assertOk()
            ->assertDontSee('dk-contact-btn--wa', false)
            ->assertDontSee('<dt>Dirección</dt>', false)
            ->assertDontSee('<dt>Horarios</dt>', false);

        $this->setting('contact', 'whatsapp_number', '573001234567');
        $this->setting('contact', 'phone', '+57 300 123 4567');
        $this->setting('contact', 'address', 'Calle 1 # 2-3, Bogotá');
        $this->setting('contact', 'business_hours', 'Lunes a viernes 8:00 a 18:00');
        $this->setting('social', 'instagram_url', 'https://instagram.com/clinica');
        $this->setting('social', 'facebook_url', 'https://facebook.com/clinica');
        $this->setting('social', 'tiktok_url', 'https://tiktok.com/@clinica');

        $this->get('/contacto')->assertOk()
            ->assertSee('https://wa.me/573001234567', false)
            ->assertSee('href="tel:+573001234567"', false)
            ->assertSee('https://instagram.com/clinica', false)
            ->assertSee('https://facebook.com/clinica', false)
            ->assertSee('https://tiktok.com/@clinica', false)
            ->assertSee('Calle 1 # 2-3, Bogotá')
            ->assertSee('Lunes a viernes 8:00 a 18:00');
    }

    public function test_el_cta_del_encabezado_y_el_menu_llevan_a_contacto(): void
    {
        // Aunque haya WhatsApp configurado, "Agenda ahora" lleva a la página propia.
        $this->setting('contact', 'whatsapp_number', '573001234567');
        $contact = route('contact');

        foreach (['/', route('services.index'), route('results'), route('team'), route('faq'), $contact] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertMatchesRegularExpression('#<a href="'.preg_quote($contact, '#').'" class="topbar-cta"#', $html, "CTA del encabezado en {$url}");
            $this->assertStringContainsString('href="'.$contact.'" class="btn btn-primary mobile-nav-cta"', $html, "CTA del menú móvil en {$url}");
            $this->assertStringNotContainsString('#formulario', $html, "Sin anclas al formulario viejo en {$url}");
        }

        // "Contacto" del menú marcado como activo en /contacto (escritorio y móvil).
        $html = $this->get('/contacto')->getContent();
        $this->assertSame(2, preg_match_all('#href="'.preg_quote($contact, '#').'" class="is-active"\s+aria-current="page"\s*>Contacto</a>#', $html));
    }
}
