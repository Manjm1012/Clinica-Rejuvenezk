<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBannersUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login_del_panel(): void
    {
        $this->post('/admin/home-banners/upload')
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_peticion_json_de_invitado_recibe_401(): void
    {
        $this->postJson('/admin/home-banners/upload')->assertUnauthorized();
    }
}
