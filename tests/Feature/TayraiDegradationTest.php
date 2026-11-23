<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Lead;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Si faltan las variables de TayrAI la app no cae: guarda el lead, no llama al CRM y avisa en el log.
 */
class TayraiDegradationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['services.tayrai.enabled' => null, 'services.tayrai.api_key' => null, 'services.tayrai.webhook_secret' => null]);
    }

    public function test_habilitado_sin_api_key_guarda_el_lead_y_avisa_una_vez(): void
    {
        Http::fake();
        config(['services.tayrai.enabled' => 'true']);
        Log::spy();

        $this->post('/contacto', ['name' => 'Ana', 'phone' => '3001112233'])->assertRedirect()->assertSessionHas('success');
        $this->post('/contacto', ['name' => 'Luis', 'phone' => '3002223344'])->assertRedirect();

        $this->assertSame(2, Lead::query()->withoutGlobalScopes()->count());
        Http::assertNothingSent();
        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message) => str_contains($message, 'TAYRAI_API_KEY'));
    }

    public function test_deshabilitado_no_avisa_ni_llama(): void
    {
        Http::fake();
        Log::spy();

        $this->post('/contacto', ['name' => 'Ana', 'phone' => '3001112233'])->assertRedirect();

        Http::assertNothingSent();
        Log::shouldNotHaveReceived('warning');
    }

    public function test_las_claves_guardadas_en_ajustes_siguen_funcionando_sin_variables(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        foreach (['enabled' => ['1', 'boolean'], 'api_key' => ['clave-en-ajustes', 'string']] as $key => [$value, $type]) {
            SiteSetting::query()->create(['clinic_id' => $clinic->id, 'group' => 'tayrai', 'key' => $key, 'value' => $value, 'type' => $type]);
        }
        Cache::flush();
        Http::fake(['*/contacts' => Http::response(['data' => ['id' => 'C-1']]), '*/leads' => Http::response(['data' => ['id' => 'L-1']])]);

        $this->post('/contacto', ['name' => 'Ana', 'phone' => '3001112233'])->assertRedirect();

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer clave-en-ajustes'));
        $this->assertSame('L-1', Lead::query()->withoutGlobalScopes()->value('tayrai_lead_id'));
    }

    public function test_la_variable_de_entorno_tiene_prioridad_sobre_ajustes(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        SiteSetting::query()->create(['clinic_id' => $clinic->id, 'group' => 'tayrai', 'key' => 'api_key', 'value' => 'clave-vieja', 'type' => 'string']);
        Cache::flush();
        config(['services.tayrai.enabled' => 'true', 'services.tayrai.api_key' => 'clave-de-env']);
        Http::fake(['*' => Http::response(['data' => ['id' => 'X']])]);

        $this->post('/contacto', ['name' => 'Ana', 'phone' => '3001112233'])->assertRedirect();

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer clave-de-env'));
    }

    public function test_webhook_sin_secreto_responde_401_y_avisa(): void
    {
        Log::spy();

        $this->postJson('/webhooks/tayrai', ['lead_id' => 'T-1', 'status' => 'contacted'], ['X-Tayrai-Signature' => 'abc'])
            ->assertStatus(401);

        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message) => str_contains($message, 'TAYRAI_WEBHOOK_SECRET'));
    }
}
