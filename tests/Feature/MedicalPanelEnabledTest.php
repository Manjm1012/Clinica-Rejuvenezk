<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * MEDICAL_PANEL_ENABLED=true: el panel médico, sus rutas y sus opciones existen.
 * (El comportamiento completo del panel se prueba en DoctorPanelTest.)
 */
class MedicalPanelEnabledTest extends TestCase
{
    use RefreshDatabase;

    protected bool $medicalPanel = true;

    public function test_con_el_flag_encendido_el_panel_existe(): void
    {
        $this->assertTrue(config('clinical.medical_panel_enabled'));
        $this->assertTrue(Route::has('filament.doctor.auth.login'));
        $this->assertTrue(Route::has('patient.prescriptions.show'));
        $this->assertTrue(Route::has('prescriptions.print'));

        $this->get('/medico/login')->assertOk();
    }

    public function test_el_admin_ofrece_el_rol_medico(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->actingAs(User::factory()->create(['clinic_id' => $clinic->id]));

        $this->get(UserResource::getUrl('create'))->assertOk()->assertSee('Perfil de médico')->assertSee('value="doctor"', false);
    }
}
