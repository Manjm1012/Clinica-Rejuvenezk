<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\CurrentClinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Correcciones de seguridad: aislamiento por clínica, medios, webhook y API.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;

    private Clinic $otherClinic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->otherClinic = Clinic::query()->create(['name' => 'Otra', 'slug' => 'otra', 'is_active' => true]);
    }

    private function service(Clinic $clinic, string $slug, bool $active = true): Service
    {
        $service = new Service(['name' => ucfirst($slug), 'slug' => $slug, 'is_active' => $active]);
        $service->clinic_id = $clinic->id;
        $service->save();

        return $service;
    }

    // ---------- Aislamiento por clínica ----------

    public function test_servicio_de_otra_clinica_no_se_ve_por_url(): void
    {
        $this->service($this->otherClinic, 'secreto-de-otra');

        $this->get('/procedimientos/secreto-de-otra')->assertNotFound();
        $this->getJson('/api/services/secreto-de-otra')->assertNotFound();
    }

    public function test_servicio_inactivo_devuelve_404(): void
    {
        $this->service($this->clinic, 'activo');
        $this->service($this->clinic, 'inactivo', active: false);

        $this->get('/procedimientos/activo')->assertOk();
        $this->get('/procedimientos/inactivo')->assertNotFound();
        $this->getJson('/api/services/inactivo')->assertNotFound();
    }

    public function test_el_scope_falla_cerrado_si_no_hay_clinica_resuelta(): void
    {
        $this->service($this->clinic, 'uno');

        app()->instance(CurrentClinic::class, new CurrentClinic(null));

        $this->assertSame(0, Service::query()->count());
    }

    // ---------- Medios públicos ----------

    public function test_media_no_expone_subidas_temporales_ni_archivos_ocultos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('livewire-tmp/sin-validar.jpg', 'x');
        Storage::disk('public')->put('branding/.env.jpg', 'x');
        Storage::disk('public')->put('branding/script.php', '<?php');
        Storage::disk('public')->put('branding/logo.webp', 'x');

        $this->get('/media/livewire-tmp/sin-validar.jpg')->assertNotFound();
        $this->get('/media/branding/.env.jpg')->assertNotFound();
        $this->get('/media/branding/script.php')->assertNotFound();
        $this->get('/media/branding/logo.webp')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_subidas_temporales_de_livewire_van_al_disco_privado(): void
    {
        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
    }

    // ---------- Webhook TayrAI ----------

    public function test_webhook_valida_firma_y_estado(): void
    {
        config(['services.tayrai.webhook_secret' => 'secreto']);
        $lead = Lead::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Ana', 'phone' => '300', 'source' => 'website', 'tayrai_lead_id' => 'T-1']);

        $send = function (array $payload, ?string $signature = null) {
            $body = json_encode($payload);

            return $this->call('POST', '/webhooks/tayrai', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_TAYRAI_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, 'secreto'),
            ], $body);
        };

        $send(['lead_id' => 'T-1', 'status' => 'converted'], 'firma-falsa')->assertStatus(401);
        $send(['lead_id' => 'T-1', 'status' => 'hackeado'])->assertStatus(422);
        $this->assertSame('new', $lead->fresh()->status);

        $send(['lead_id' => 'T-1', 'status' => 'contacted'])->assertOk();
        $this->assertSame('contacted', $lead->fresh()->status);
    }

    // ---------- API ----------

    public function test_api_de_leads_no_devuelve_metadatos_y_sincroniza_con_tayrai(): void
    {
        config(['services.tayrai.enabled' => 'true', 'services.tayrai.api_key' => 'k']);
        Http::fake([
            '*/contacts' => Http::response(['data' => ['id' => 'C-9']]),
            '*/leads' => Http::response(['data' => ['id' => 'L-9']]),
        ]);

        $response = $this->postJson('/api/leads', ['name' => 'Luis', 'phone' => '3001112233'])->assertCreated();

        $this->assertArrayNotHasKey('metadata', $response->json('data'));
        $this->assertArrayNotHasKey('tayrai_contact_id', $response->json('data'));

        $lead = Lead::query()->withoutGlobalScope('clinic')->findOrFail($response->json('data.id'));
        $this->assertSame('L-9', $lead->tayrai_lead_id);
        $this->assertSame($this->clinic->id, $lead->clinic_id);
    }

    public function test_formulario_ignora_un_servicio_de_otra_clinica(): void
    {
        $foreign = $this->service($this->otherClinic, 'ajeno');

        $this->post('/contacto', ['name' => 'Eva', 'phone' => '3001112233', 'service_id' => $foreign->id])->assertRedirect();

        $lead = Lead::query()->withoutGlobalScope('clinic')->where('name', 'Eva')->firstOrFail();
        $this->assertNull($lead->service_id);
    }

    public function test_api_me_solo_devuelve_campos_publicos_y_los_tokens_vencen(): void
    {
        $user = User::factory()->create(['clinic_id' => $this->clinic->id]);
        $token = $user->createToken('test')->plainTextToken;

        $data = $this->withToken($token)->getJson('/api/auth/me')->assertOk()->json('user');

        $this->assertSame(['id', 'name', 'email', 'clinic_id', 'role', 'is_super_admin'], array_keys($data));
        $this->assertNotNull(config('sanctum.expiration'));
    }

    public function test_los_secretos_no_se_muestran_en_ajustes(): void
    {
        $this->actingAs(User::factory()->create(['clinic_id' => $this->clinic->id]));
        SiteSetting::query()->create(['clinic_id' => $this->clinic->id, 'group' => 'tayrai', 'key' => 'api_key', 'value' => 'sk-super-secreta', 'type' => 'string']);

        $this->get(SiteSettingResource::getUrl('index'))->assertOk()->assertDontSee('sk-super-secreta');
    }
}
