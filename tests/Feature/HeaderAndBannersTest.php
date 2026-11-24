<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Encabezado (CTA siempre presente, hamburguesa accesible) y carrusel de banners de la portada.
 * El diseño responsive (sticky, tamaños, desbordes) se revisó en navegador a 360–1920px.
 */
class HeaderAndBannersTest extends TestCase
{
    use RefreshDatabase;

    private function banners(int $count): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();

        foreach (range(1, $count) as $n) {
            $setting = new SiteSetting(['group' => 'branding', 'key' => "home_banner_{$n}_image", 'value' => "https://example.com/banner-{$n}.jpg", 'type' => 'string']);
            $setting->clinic_id = $clinic->id;
            $setting->save();
        }

        Cache::flush();
    }

    public function test_todas_las_paginas_publicas_tienen_cta_y_menu_accesible_en_la_barra(): void
    {
        foreach (['/', route('services.index'), route('results'), route('team'), route('contact'), route('patient.login'), route('patient.register')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<header class="topbar">.*class="topbar-cta".*</header>#s', $html, "CTA dentro de la barra en {$url}");
            $this->assertStringContainsString('aria-controls="mobile-nav-panel"', $html, "Hamburguesa en {$url}");
            $this->assertStringContainsString('aria-label="Abrir menú de navegación"', $html);
            $this->assertMatchesRegularExpression('#class="brand brand-lockup" aria-label="Ir al inicio de [^"]+"#', $html, "El logo conserva el nombre en {$url}");
        }
    }

    public function test_el_carrusel_de_banners_es_accesible_y_separa_los_puntos_del_texto(): void
    {
        $this->banners(3);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('aria-roledescription="carrusel"', $html);
        $this->assertStringContainsString('tabindex="0"', $html); // foco para usar ← →
        $this->assertSame(3, substr_count($html, 'aria-roledescription="diapositiva"'));
        $this->assertStringContainsString('aria-label="1 de 3: Resultados naturales con criterio médico"', $html);
        // Solo la primera diapositiva es visible para lectores de pantalla al cargar.
        $this->assertSame(2, preg_match_all('#<article class="banner-slide[^>]*aria-hidden="true"#s', $html));
        $this->assertStringContainsString('aria-label="Banner anterior"', $html);
        $this->assertStringContainsString('aria-label="Ir a banner 3"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);

        // Los puntos van fuera del bloque de texto de cada diapositiva.
        preg_match_all('#<div class="banner-slide-overlay">(.*?)</div>#s', $html, $overlays);
        $this->assertCount(3, $overlays[1]);
        foreach ($overlays[1] as $overlay) {
            $this->assertStringNotContainsString('banner-indicator', $overlay);
        }
    }

    public function test_con_un_solo_banner_no_hay_flechas_ni_puntos(): void
    {
        $this->banners(1);

        $this->get('/')->assertOk()
            ->assertSee('class="banner-slide-media"', false)
            ->assertDontSee('banner-prev', false)
            ->assertDontSee('banner-indicators', false);
    }
}
