<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Portal del paciente. Corre contra SQLite en memoria (phpunit.xml): no toca la base local.
 * Ojo: el global scope de BelongsToClinic se desactiva en consola, así que estas pruebas
 * validan el aislamiento explícito (where clinic_id, relación por patient_id, middleware).
 */
class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;

    private Clinic $otherClinic;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');

        // La migración de clínicas crea la clínica 1 (la que resuelve ResolveClinic por defecto).
        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->otherClinic = Clinic::query()->create(['name' => 'Otra clínica', 'slug' => 'otra', 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makePatient(Clinic $clinic, array $attributes = []): Patient
    {
        $patient = new Patient(array_merge([
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'phone' => '3001234567',
            'password' => 'secreta123',
        ], $attributes));
        $patient->clinic_id = $clinic->id;
        $patient->save();

        return $patient;
    }

    private function makeTreatment(Patient $patient, array $attributes = []): PatientTreatment
    {
        $treatment = new PatientTreatment(array_merge([
            'patient_id' => $patient->id,
            'performed_at' => '2026-10-01',
        ], $attributes));
        $treatment->clinic_id = $patient->clinic_id;
        $treatment->save();

        return $treatment;
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Laura Gómez',
            'email' => 'Laura@Example.com',
            'phone' => '+57 310 555 1234',
            'password' => 'clave-segura-1',
            'password_confirmation' => 'clave-segura-1',
            'data_policy' => '1',
        ], $overrides);
    }

    // ---------- Registro ----------

    public function test_registro_correcto_normaliza_datos_y_crea_lead(): void
    {
        $response = $this->post('/registro', $this->registrationData());

        $response->assertRedirect(route('patient.dashboard'));
        $this->assertAuthenticated('patient');

        $patient = Patient::query()->firstOrFail();
        $this->assertSame($this->clinic->id, $patient->clinic_id);
        $this->assertSame('laura@example.com', $patient->email);
        $this->assertSame('3105551234', $patient->phone); // sin indicativo 57
        $this->assertFalse($patient->marketing_consent);   // checkbox opcional no marcado
        $this->assertNull($patient->marketing_consent_at);
        $this->assertNotNull($patient->data_policy_accepted_at);
        $this->assertSame(Patient::DATA_POLICY_VERSION, $patient->data_policy_version);
        $this->assertTrue(Hash::check('clave-segura-1', $patient->password));

        $lead = Lead::query()->findOrFail($patient->lead_id);
        $this->assertSame('portal_paciente', $lead->source);
    }

    public function test_registro_vincula_lead_existente_y_lo_convierte(): void
    {
        $lead = Lead::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Laura', 'phone' => '310 555 1234', 'source' => 'website']);

        $this->post('/registro', $this->registrationData(['marketing_consent' => '1']))->assertRedirect(route('patient.dashboard'));

        $patient = Patient::query()->firstOrFail();
        $this->assertSame($lead->id, $patient->lead_id);
        $this->assertSame('converted', $lead->fresh()->status);
        $this->assertTrue($patient->marketing_consent);
        $this->assertNotNull($patient->marketing_consent_at);
        $this->assertSame(1, Lead::query()->count());
    }

    public function test_registro_sin_aceptar_politica_falla(): void
    {
        $response = $this->post('/registro', $this->registrationData(['data_policy' => null]));

        $response->assertSessionHasErrors('data_policy');
        $this->assertGuest('patient');
        $this->assertSame(0, Patient::query()->count());
    }

    public function test_registro_con_email_existente_en_la_clinica_no_activa_la_cuenta(): void
    {
        $existing = $this->makePatient($this->clinic, ['email' => 'laura@example.com', 'phone' => '3200000000', 'password' => null]);

        $response = $this->post('/registro', $this->registrationData());

        $response->assertSessionHas('patient_exists', true);
        $this->assertGuest('patient');
        $this->assertNull($existing->fresh()->password);
        $this->assertSame(1, Patient::query()->count());
    }

    public function test_registro_con_telefono_existente_no_activa_ni_cambia_la_cuenta(): void
    {
        $existing = $this->makePatient($this->clinic, ['email' => 'otra@example.com', 'phone' => '3105551234', 'password' => 'original-123']);

        $response = $this->post('/registro', $this->registrationData(['email' => 'nuevo@example.com']));

        $response->assertSessionHas('patient_exists', true);
        $this->assertGuest('patient');
        $this->assertTrue(Hash::check('original-123', $existing->fresh()->password));
        $this->assertSame('otra@example.com', $existing->fresh()->email);
        $this->assertSame(1, Patient::query()->count());

        // La página muestra el aviso con el enlace para activar la cuenta.
        $this->get('/registro')->assertSee('Ya tenemos un registro con estos datos');
    }

    public function test_el_mismo_correo_puede_registrarse_en_otra_clinica(): void
    {
        $this->makePatient($this->otherClinic, ['email' => 'laura@example.com', 'phone' => '3105551234']);

        $this->post('/registro', $this->registrationData())->assertRedirect(route('patient.dashboard'));

        $this->assertSame(1, Patient::query()->where('clinic_id', $this->clinic->id)->count());
    }

    // ---------- Login / logout ----------

    public function test_login_con_email(): void
    {
        $patient = $this->makePatient($this->clinic);

        $this->post('/ingresar', ['login' => ' ANA@example.com ', 'password' => 'secreta123'])
            ->assertRedirect(route('patient.dashboard'));

        $this->assertAuthenticatedAs($patient, 'patient');
    }

    public function test_login_con_telefono_en_cualquier_formato(): void
    {
        $patient = $this->makePatient($this->clinic);

        $this->post('/ingresar', ['login' => '+57 300 123 4567', 'password' => 'secreta123'])
            ->assertRedirect(route('patient.dashboard'));

        $this->assertAuthenticatedAs($patient, 'patient');
    }

    public function test_login_fallido_usa_mensaje_generico(): void
    {
        $this->makePatient($this->clinic);
        $this->makePatient($this->clinic, ['email' => 'sinclave@example.com', 'phone' => '3110000000', 'password' => null]);

        $wrongPassword = $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'incorrecta']);
        $unknown = $this->post('/ingresar', ['login' => 'nadie@example.com', 'password' => 'secreta123']);
        $noPassword = $this->post('/ingresar', ['login' => 'sinclave@example.com', 'password' => 'secreta123']);

        foreach ([$wrongPassword, $unknown, $noPassword] as $response) {
            $response->assertSessionHasErrors(['login' => 'Los datos de acceso no son correctos.']);
        }
        $this->assertGuest('patient');
    }

    public function test_login_no_encuentra_pacientes_de_otra_clinica(): void
    {
        $this->makePatient($this->otherClinic);

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123'])
            ->assertSessionHasErrors('login');

        $this->assertGuest('patient');
    }

    public function test_login_tiene_limite_de_intentos(): void
    {
        $this->makePatient($this->clinic);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'mal']);
        }

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123'])
            ->assertSessionHasErrors(['login' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.']);
        $this->assertGuest('patient');
    }

    public function test_logout(): void
    {
        $this->makePatient($this->clinic);
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->post('/salir')->assertRedirect(route('home'));

        $this->assertGuest('patient');
    }

    // ---------- Acceso ----------

    public function test_invitado_en_mi_cuenta_es_redirigido_a_ingresar(): void
    {
        $this->get('/mi-cuenta')->assertRedirect(route('patient.login'));
        $this->get('/mi-cuenta/perfil')->assertRedirect(route('patient.login'));
    }

    public function test_paciente_logueado_no_accede_al_admin(): void
    {
        $this->makePatient($this->clinic);
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);
        $this->assertAuthenticated('patient');

        $response = $this->get('/admin');

        $response->assertRedirect();
        $this->assertStringContainsString('/admin/login', $response->headers->get('Location'));
        $this->assertGuest('web');
    }

    public function test_cambiar_de_clinica_con_query_cierra_la_sesion_del_paciente(): void
    {
        $this->makePatient($this->clinic);
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);
        $this->get('/mi-cuenta')->assertOk();

        $this->get('/mi-cuenta?clinic=otra')->assertRedirect(route('patient.login'));
        $this->assertGuest('patient');

        // De vuelta en la clínica original la sesión ya no existe.
        $this->get('/mi-cuenta')->assertRedirect(route('patient.login'));
    }

    // ---------- Dashboard ----------

    public function test_paciente_solo_ve_sus_tratamientos(): void
    {
        $ana = $this->makePatient($this->clinic);
        $otro = $this->makePatient($this->clinic, ['email' => 'otro@example.com', 'phone' => '3009999999']);
        $otraClinica = $this->makePatient($this->otherClinic, ['email' => 'x@example.com', 'phone' => '3008888888']);

        $this->makeTreatment($ana, ['notes' => 'NOTA-DE-ANA']);
        $this->makeTreatment($otro, ['notes' => 'NOTA-DE-OTRO-PACIENTE']);
        $this->makeTreatment($otraClinica, ['notes' => 'NOTA-DE-OTRA-CLINICA']);

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->get('/mi-cuenta')
            ->assertOk()
            ->assertSee('NOTA-DE-ANA')
            ->assertDontSee('NOTA-DE-OTRO-PACIENTE')
            ->assertDontSee('NOTA-DE-OTRA-CLINICA');
    }

    public function test_internal_notes_no_aparece_en_el_dashboard(): void
    {
        $ana = $this->makePatient($this->clinic);
        $service = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Toxina botulínica', 'slug' => 'toxina', 'renewal_interval_days' => 180]);
        $this->makeTreatment($ana, [
            'service_id' => $service->id,
            'notes' => 'Nota visible para la paciente',
            'internal_notes' => 'SECRETO-INTERNO-NO-MOSTRAR',
        ]);

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->get('/mi-cuenta')
            ->assertOk()
            ->assertSee('Toxina botulínica')
            ->assertSee('Nota visible para la paciente')
            ->assertDontSee('SECRETO-INTERNO-NO-MOSTRAR');
    }

    public function test_agendar_renovacion_abre_whatsapp_con_el_nombre_del_servicio(): void
    {
        \App\Models\SiteSetting::query()->create(['clinic_id' => $this->clinic->id, 'group' => 'contact', 'key' => 'whatsapp_number', 'value' => '573001234567', 'type' => 'string']);
        \Illuminate\Support\Facades\Cache::flush();

        $ana = $this->makePatient($this->clinic);
        $service = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Toxina botulínica', 'slug' => 'toxina', 'renewal_interval_days' => 180, 'whatsapp_text' => 'Texto de catálogo']);
        $this->makeTreatment($ana, ['service_id' => $service->id, 'performed_at' => '2026-01-01']); // vencido

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $expected = 'https://wa.me/573001234567?text='.urlencode('Hola, quiero agendar la renovación de Toxina botulínica');
        $this->get('/mi-cuenta')->assertOk()->assertSee(e($expected), false)->assertDontSee('Texto de catálogo');
    }

    public function test_dashboard_vacio_muestra_mensaje_amable(): void
    {
        $this->makePatient($this->clinic);
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->get('/mi-cuenta')->assertOk()->assertSee('Aún no tienes procedimientos registrados');
    }

    // ---------- Estado de renovación ----------

    public function test_renewal_status(): void
    {
        $service = new Service(['renewal_interval_days' => 180]);

        $vigente = (new PatientTreatment(['performed_at' => '2026-11-01']))->setRelation('service', $service);
        $proxima = (new PatientTreatment(['performed_at' => '2026-05-01', 'next_due_at' => '2026-11-25']))->setRelation('service', null);
        $vencida = (new PatientTreatment(['performed_at' => '2026-01-01']))->setRelation('service', $service); // vence 2026-06-30
        $noAplica = (new PatientTreatment(['performed_at' => '2026-10-01']))->setRelation('service', new Service);

        $this->assertSame('vigente', $vigente->renewal_status);
        $this->assertSame('2027-04-30', $vigente->due_date->toDateString());
        $this->assertSame('proxima', $proxima->renewal_status);
        $this->assertSame('vencido', $vencida->renewal_status);
        $this->assertSame('no_aplica', $noAplica->renewal_status);
        $this->assertNull($noAplica->due_date);
    }

    public function test_renewal_status_usa_la_hora_de_colombia_y_no_utc(): void
    {
        // 19:30 en Bogotá = 00:30 del día siguiente en UTC.
        Carbon::setTestNow(Carbon::parse('2026-11-10 19:30:00', 'America/Bogota'));
        $this->assertSame('2026-11-11', Carbon::now('UTC')->toDateString());

        $ana = $this->makePatient($this->clinic);
        $venceHoy = $this->makeTreatment($ana, ['next_due_at' => '2026-11-10']);
        $vencioAyer = $this->makeTreatment($ana, ['next_due_at' => '2026-11-09']);
        $limiteProxima = $this->makeTreatment($ana, ['next_due_at' => '2026-12-10']); // hoy + 30 días en Bogotá

        $this->assertSame('America/Bogota', PatientTreatment::timezoneForClinic($this->clinic->id));
        $this->assertSame('proxima', $venceHoy->fresh()->renewal_status); // en UTC sería "vencido"
        $this->assertSame('vencido', $vencioAyer->fresh()->renewal_status);
        $this->assertSame('proxima', $limiteProxima->fresh()->renewal_status);

        // El dashboard muestra el mismo estado.
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);
        $this->get('/mi-cuenta')->assertOk()->assertSeeInOrder(['Renovar pronto', 'Vencido']);
    }

    // ---------- Perfil ----------

    public function test_perfil_cambia_contrasena_solo_con_la_actual(): void
    {
        $ana = $this->makePatient($this->clinic);
        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->put('/mi-cuenta/perfil', [
            'name' => 'Ana P.', 'phone' => '3001234567',
            'current_password' => 'equivocada', 'password' => 'nueva-clave-1', 'password_confirmation' => 'nueva-clave-1',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('secreta123', $ana->fresh()->password));

        $this->put('/mi-cuenta/perfil', [
            'name' => 'Ana P.', 'phone' => '3001234567', 'marketing_consent' => '1',
            'current_password' => 'secreta123', 'password' => 'nueva-clave-1', 'password_confirmation' => 'nueva-clave-1',
        ])->assertRedirect(route('patient.profile'));

        $ana->refresh();
        $this->assertSame('Ana P.', $ana->name);
        $this->assertTrue(Hash::check('nueva-clave-1', $ana->password));
        $this->assertTrue($ana->marketing_consent);
        $this->assertNotNull($ana->marketing_consent_at);
    }

    // ---------- Páginas públicas ----------

    public function test_paginas_publicas_del_portal_responden(): void
    {
        $this->get('/ingresar')->assertOk()->assertSee('Ingresa a tu cuenta');
        $this->get('/registro')->assertOk()->assertSee('Crea tu cuenta');
        $this->get('/politica-de-datos')->assertOk()->assertSee('Borrador: revisar con asesor legal antes de publicar.');
        $this->get('/')->assertOk()->assertSee(route('legal.data-policy'), false)->assertSee('Ingresar');
    }
}
