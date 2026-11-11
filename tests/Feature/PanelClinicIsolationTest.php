<?php

namespace Tests\Feature;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\LeadResource;
use App\Filament\Resources\PatientResource;
use App\Filament\Resources\ServiceResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\AdminClinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Tests\TestCase;

/**
 * Aislamiento por clínica en /admin (middleware persistente SetAdminClinic).
 * El host de las pruebas (localhost) hace que ResolveClinic elija la clínica 1:
 * si el middleware no corriera, un admin de la clínica 2 vería/crearía datos de la clínica 1.
 */
class PanelClinicIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinicOne;

    private Clinic $clinicTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinicOne = Clinic::query()->orderBy('id')->firstOrFail();
        $this->clinicTwo = Clinic::query()->create(['name' => 'Clínica Dos', 'slug' => 'clinica-dos', 'is_active' => true]);

        foreach ([[$this->clinicOne, 'Uno'], [$this->clinicTwo, 'Dos']] as [$clinic, $label]) {
            Lead::query()->create(['clinic_id' => $clinic->id, 'name' => "Lead {$label}", 'phone' => '300', 'source' => 'website']);
            Appointment::query()->create(['clinic_id' => $clinic->id, 'patient_name' => "Cita {$label}", 'patient_phone' => '300', 'appointment_at' => now()->addDay()]);
            Service::query()->create(['clinic_id' => $clinic->id, 'name' => "Servicio {$label}", 'slug' => 'servicio-'.mb_strtolower($label)]);

            $patient = new Patient(['name' => "Paciente {$label}", 'phone' => '30000000'.($label === 'Uno' ? '01' : '02')]);
            $patient->clinic_id = $clinic->id;
            $patient->save();
        }
    }

    private function adminOf(?Clinic $clinic, bool $superAdmin = false): User
    {
        return User::factory()->create(['clinic_id' => $clinic?->id, 'is_super_admin' => $superAdmin]);
    }

    public function test_admin_de_la_clinica_2_no_ve_datos_de_la_clinica_1(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));

        $pages = [
            LeadResource::getUrl('index') => ['Lead Dos', 'Lead Uno'],
            AppointmentResource::getUrl('index') => ['Cita Dos', 'Cita Uno'],
            ServiceResource::getUrl('index') => ['Servicio Dos', 'Servicio Uno'],
            PatientResource::getUrl('index') => ['Paciente Dos', 'Paciente Uno'],
        ];

        foreach ($pages as $url => [$visible, $hidden]) {
            $this->get($url)->assertOk()->assertSee($visible)->assertDontSee($hidden);
        }

        // Tampoco por URL directa.
        $serviceOne = Service::query()->withoutGlobalScope('clinic')->where('slug', 'servicio-uno')->firstOrFail();
        $this->get(ServiceResource::getUrl('edit', ['record' => $serviceOne]))->assertNotFound();
    }

    public function test_super_admin_sin_clinica_entra_y_ve_la_clinica_por_defecto(): void
    {
        $this->actingAs($this->adminOf(null, superAdmin: true));

        $this->get('/admin')->assertOk();
        $this->get(LeadResource::getUrl('index'))->assertOk()->assertSee('Lead Uno')->assertDontSee('Lead Dos');
    }

    public function test_crear_un_servicio_desde_el_panel_via_livewire_asigna_la_clinica_del_usuario(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));

        $this->livewireCall(ServiceResource::getUrl('create'), 'create-service', [
            'data.name' => 'Peeling químico',
            'data.slug' => 'peeling-quimico',
        ], 'create')->assertOk();

        $service = Service::query()->withoutGlobalScope('clinic')->where('slug', 'peeling-quimico')->firstOrFail();
        $this->assertSame($this->clinicTwo->id, $service->clinic_id);
    }

    /**
     * La regla unique del slug filtra por clinic_id. El caso "otra clínica puede repetir el slug"
     * no se prueba aquí: en SQLite services.slug sigue con único global (la migración de únicos
     * por clínica solo corre en MySQL, donde el índice es clinic_id + slug).
     */
    public function test_slug_repetido_en_la_misma_clinica_se_rechaza(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));

        // El nombre genera el slug automáticamente: "Servicio Dos" -> "servicio-dos" (ya existe en la clínica 2).
        $response = $this->livewireCall(ServiceResource::getUrl('create'), 'create-service', [
            'data.name' => 'Servicio Dos',
            'data.slug' => 'servicio-dos',
        ], 'create')->assertOk();

        $this->assertSame(1, Service::query()->withoutGlobalScope('clinic')->where('slug', 'servicio-dos')->count());
        $this->assertStringContainsString('data.slug', $response->getContent()); // error de validación
    }

    public function test_la_regla_unique_del_slug_filtra_por_la_clinica_del_usuario(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));

        $rule = Rule::unique('services', 'slug')
            ->where('clinic_id', AdminClinic::idOrNone());

        $this->assertTrue(validator(['slug' => 'servicio-uno'], ['slug' => [$rule]])->passes()); // existe solo en la clínica 1
        $this->assertTrue(validator(['slug' => 'servicio-dos'], ['slug' => [$rule]])->fails());
    }

    public function test_editar_un_servicio_via_livewire_respeta_el_aislamiento(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));
        $serviceTwo = Service::query()->withoutGlobalScope('clinic')->where('slug', 'servicio-dos')->firstOrFail();

        $this->livewireCall(ServiceResource::getUrl('edit', ['record' => $serviceTwo]), 'edit-service', [
            'data.name' => 'Servicio Dos editado',
        ], 'save')->assertOk();

        $serviceTwo->refresh();
        $this->assertSame('Servicio Dos editado', $serviceTwo->name);
        $this->assertSame($this->clinicTwo->id, $serviceTwo->clinic_id);
        $this->assertSame('Servicio Uno', Service::query()->withoutGlobalScope('clinic')->where('slug', 'servicio-uno')->value('name'));
    }

    public function test_crear_un_lead_desde_el_panel_via_livewire_asigna_la_clinica_del_usuario(): void
    {
        $this->actingAs($this->adminOf($this->clinicTwo));

        $this->livewireCall(LeadResource::getUrl('create'), 'create-lead', [
            'data.name' => 'Lead creado en panel',
            'data.phone' => '3001112233',
            'data.status' => 'new',
            'data.source' => 'whatsapp', // el formulario existente de leads no tiene default para source (NOT NULL)
        ], 'create')->assertOk();

        $lead = Lead::query()->withoutGlobalScope('clinic')->where('name', 'Lead creado en panel')->firstOrFail();
        $this->assertSame($this->clinicTwo->id, $lead->clinic_id);
    }

    /**
     * Petición real a /livewire/update (pasa por el grupo web y por los middleware persistentes),
     * usando el snapshot del componente renderizado en la página.
     */
    private function livewireCall(string $url, string $componentSuffix, array $updates, string $method): TestResponse
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = collect($matches[1])
            ->map(fn (string $raw) => html_entity_decode($raw, ENT_QUOTES))
            ->first(fn (string $json) => str_ends_with(json_decode($json, true)['memo']['name'] ?? '', $componentSuffix));

        $this->assertNotNull($snapshot, "No se encontró el componente {$componentSuffix} en {$url}");

        return $this->withHeaders(['X-Livewire' => 'true'])->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => $updates,
                'calls' => [['path' => '', 'method' => $method, 'params' => []]],
            ]],
        ]);
    }
}
