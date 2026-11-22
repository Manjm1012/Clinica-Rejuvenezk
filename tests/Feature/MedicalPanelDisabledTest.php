<?php

namespace Tests\Feature;

use App\Filament\Resources\DoctorResource;
use App\Filament\Resources\UserResource;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CallsLivewireOverHttp;
use Tests\TestCase;

/**
 * MEDICAL_PANEL_ENABLED=false (valor por defecto): el panel médico y las fórmulas no existen.
 */
class MedicalPanelDisabledTest extends TestCase
{
    use CallsLivewireOverHttp, RefreshDatabase;

    protected bool $medicalPanel = false;

    private Clinic $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
    }

    private function issuedPrescriptionFor(Patient $patient): Prescription
    {
        $doctor = new Doctor(['name' => 'Dr. Kevin', 'is_active' => true]);
        $doctor->clinic_id = $this->clinic->id;
        $doctor->save();

        $prescription = new Prescription(['care_instructions' => 'Protector solar']);
        $prescription->clinic_id = $this->clinic->id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $doctor->id;
        $prescription->save();
        $prescription->issue();

        return $prescription;
    }

    public function test_el_valor_por_defecto_es_apagado(): void
    {
        $this->assertFalse(config('clinical.medical_panel_enabled'));
        $this->assertFalse(Route::has('filament.doctor.auth.login'));
        $this->assertFalse(Route::has('patient.prescriptions.show'));
        $this->assertFalse(Route::has('prescriptions.print'));
    }

    public function test_las_rutas_del_panel_medico_dan_404(): void
    {
        $admin = User::factory()->create(['clinic_id' => $this->clinic->id]);

        foreach (['/medico', '/medico/login', '/medico/pacientes', '/medico/consultas', '/medico/formulas'] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->actingAs($admin)->get('/medico')->assertNotFound();
        $this->actingAs($admin)->get('/formulas/1/imprimir')->assertNotFound();
    }

    public function test_el_portal_no_muestra_ni_sirve_formulas(): void
    {
        $patient = new Patient(['name' => 'Ana Pérez', 'email' => 'ana@example.com', 'phone' => '3001234567', 'password' => 'secreta123']);
        $patient->clinic_id = $this->clinic->id;
        $patient->save();
        $prescription = $this->issuedPrescriptionFor($patient);

        $this->post('/ingresar', ['login' => 'ana@example.com', 'password' => 'secreta123']);

        $this->get('/mi-cuenta')->assertOk()->assertDontSee('Mis fórmulas')->assertDontSee('/mi-cuenta/formulas', false);
        $this->get('/mi-cuenta/formulas/'.$prescription->id)->assertNotFound();
    }

    public function test_el_admin_no_ofrece_el_rol_medico_ni_datos_de_formula(): void
    {
        $doctor = new Doctor(['name' => 'Dra. Ruiz', 'is_active' => true]);
        $doctor->clinic_id = $this->clinic->id;
        $doctor->save();

        $this->actingAs(User::factory()->create(['clinic_id' => $this->clinic->id]));

        $this->get(UserResource::getUrl('create'))->assertOk()
            ->assertDontSee('Perfil de médico')
            ->assertDontSee('solo el panel médico')
            ->assertDontSee('value="doctor"', false);
        $this->get(DoctorResource::getUrl('edit', ['record' => $doctor]))->assertOk()
            ->assertDontSee('Datos para la fórmula médica');

        // Aunque se manipule la petición, no se crea un usuario con rol médico.
        $this->livewireCall(UserResource::getUrl('create'), 'create-user', [
            'data.name' => 'Intruso',
            'data.email' => 'intruso@clinica.test',
            'data.password' => 'una-clave-segura',
            'data.role' => User::ROLE_DOCTOR,
        ], 'create');

        $this->assertFalse(User::query()->where('email', 'intruso@clinica.test')->exists());
    }

    public function test_las_paginas_publicas_no_enlazan_al_panel_medico(): void
    {
        foreach (['/', '/equipo-medico', '/servicios', '/ingresar'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('/medico', false);
        }
    }
}
