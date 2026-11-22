<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Filament\Doctor\Resources\PatientResource;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Filament\Doctor\Resources\ServiceRequestResource;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\Concerns\CallsLivewireOverHttp;
use Tests\TestCase;

/**
 * Panel médico (/medico): acceso, aislamiento, consultas firmadas y fórmulas.
 */
class DoctorPanelTest extends TestCase
{
    use CallsLivewireOverHttp, RefreshDatabase;

    private Clinic $clinic;

    private Clinic $otherClinic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->otherClinic = Clinic::query()->create(['name' => 'Otra', 'slug' => 'otra', 'is_active' => true]);

        // Las URLs de los recursos se generan para el panel médico (/medico).
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    /** @return array{0: User, 1: Doctor} */
    private function doctorUser(?Clinic $clinic = null, string $name = 'Dra. Ruiz', string $role = User::ROLE_DOCTOR): array
    {
        $clinic ??= $this->clinic;
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => $role]);

        $doctor = new Doctor(['name' => $name, 'is_active' => true, 'professional_registration' => 'RM-12345']);
        $doctor->clinic_id = $clinic->id;
        $doctor->save();
        $doctor->user_id = $user->id;
        $doctor->save();

        return [$user, $doctor];
    }

    private function patient(?Clinic $clinic = null, string $name = 'Ana Pérez', string $phone = '3001234567'): Patient
    {
        $patient = new Patient(['name' => $name, 'phone' => $phone, 'email' => strtolower(str_replace(' ', '', $name)).'@example.com', 'password' => 'secreta123']);
        $patient->clinic_id = ($clinic ?? $this->clinic)->id;
        $patient->save();

        return $patient;
    }

    private function consultation(Patient $patient, Doctor $doctor, array $attributes = []): Consultation
    {
        $consultation = new Consultation(array_merge(['consulted_at' => now(), 'reason' => 'Arrugas de expresión'], $attributes));
        $consultation->clinic_id = $patient->clinic_id;
        $consultation->patient_id = $patient->id;
        $consultation->doctor_id = $doctor->id;
        $consultation->save();

        return $consultation;
    }

    private function prescription(Patient $patient, Doctor $doctor, array $attributes = []): Prescription
    {
        $prescription = new Prescription(array_merge(['care_instructions' => 'Protector solar FPS 50 cada 3 horas.'], $attributes));
        $prescription->clinic_id = $patient->clinic_id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $doctor->id;
        $prescription->save();

        return $prescription;
    }

    // ---------- Acceso ----------

    public function test_solo_usuarios_con_perfil_medico_entran_al_panel(): void
    {
        [$doctorUser] = $this->doctorUser();
        $this->actingAs($doctorUser)->get('/medico')->assertOk();

        $reception = User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_RECEPTION]);
        $this->actingAs($reception)->get('/medico')->assertForbidden();

        // Un administrador que también atiende pacientes entra a ambos paneles.
        [$adminDoctor] = $this->doctorUser(name: 'Dr. Kevin', role: User::ROLE_ADMIN);
        $this->actingAs($adminDoctor)->get('/medico')->assertOk();
        $this->actingAs($adminDoctor)->get('/admin')->assertOk();
    }

    public function test_perfil_medico_inactivo_no_entra(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $doctor->update(['is_active' => false]);

        $this->actingAs($user)->get('/medico')->assertForbidden();
    }

    public function test_medico_solo_ve_pacientes_de_su_clinica(): void
    {
        [$user] = $this->doctorUser();
        $this->patient(name: 'Paciente Propia');
        $foreign = $this->patient($this->otherClinic, 'Paciente Ajena', '3009999999');

        $this->actingAs($user);
        $this->get(PatientResource::getUrl('index'))->assertOk()->assertSee('Paciente Propia')->assertDontSee('Paciente Ajena');
        $this->get(PatientResource::getUrl('view', ['record' => $foreign]))->assertNotFound();
    }

    public function test_abrir_la_historia_queda_auditado(): void
    {
        [$user] = $this->doctorUser();
        $patient = $this->patient();

        $this->actingAs($user)->get(PatientResource::getUrl('view', ['record' => $patient]))->assertOk()->assertSee('Historia de Ana Pérez');

        $this->assertTrue(AuditLog::query()
            ->where('action', 'viewed')->where('auditable_type', Patient::class)->where('auditable_id', $patient->id)
            ->where('actor_id', $user->id)->exists());
    }

    // ---------- Pacientes y consultas ----------

    public function test_medico_registra_un_paciente_con_informacion_clinica_cifrada(): void
    {
        [$user] = $this->doctorUser();
        $this->actingAs($user);

        $this->livewireCall(PatientResource::getUrl('create'), 'create-patient', [
            'data.name' => 'Laura Gómez',
            'data.phone' => '310 555 1234',
            'data.document_number' => '1020304050',
            'data.allergies' => 'Lidocaína',
        ], 'create')->assertOk();

        $patient = Patient::query()->withoutGlobalScope('clinic')->where('name', 'Laura Gómez')->firstOrFail();
        $this->assertSame($this->clinic->id, $patient->clinic_id);
        $this->assertSame('3105551234', $patient->phone);
        $this->assertSame('Lidocaína', $patient->allergies);
        $this->assertStringNotContainsString('Lidocaína', (string) DB::table('patients')->where('id', $patient->id)->value('allergies'));
    }

    public function test_crear_consulta_asigna_medico_y_clinica_y_cifra_el_texto(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $patient = $this->patient();
        $this->actingAs($user);

        $this->livewireCall(ConsultationResource::getUrl('create', ['patient' => $patient->id]), 'create-consultation', [
            'data.reason' => 'Valoración de surcos nasogenianos',
            'data.diagnosis' => 'Envejecimiento facial leve',
        ], 'create')->assertOk();

        $consultation = Consultation::query()->withoutGlobalScope('clinic')->latest('id')->firstOrFail();
        $this->assertSame($doctor->id, $consultation->doctor_id);
        $this->assertSame($patient->id, $consultation->patient_id);
        $this->assertSame($this->clinic->id, $consultation->clinic_id);
        $this->assertSame(Consultation::STATUS_DRAFT, $consultation->status);
        $this->assertStringNotContainsString('surcos', (string) DB::table('consultations')->where('id', $consultation->id)->value('reason'));
    }

    public function test_consulta_firmada_no_se_modifica_y_admite_notas_aclaratorias(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $consultation = $this->consultation($this->patient(), $doctor);
        $this->actingAs($user);

        $consultation->sign();
        $this->assertNotNull($consultation->fresh()->signed_at);

        try {
            $consultation->fresh()->update(['reason' => 'Cambiado']);
            $this->fail('Una consulta firmada no debería poder modificarse.');
        } catch (LogicException) {
            // esperado
        }

        $consultation->addAddendum('Se aclara la dosis: 20 U.', $user);

        $this->get(ConsultationResource::getUrl('view', ['record' => $consultation]))
            ->assertOk()->assertSee('Arrugas de expresión')->assertSee('Se aclara la dosis: 20 U.');
        $this->get(ConsultationResource::getUrl('edit', ['record' => $consultation]))->assertForbidden();
        $this->assertTrue(AuditLog::query()->where('action', 'signed')->where('auditable_id', $consultation->id)->exists());
    }

    public function test_un_medico_no_edita_el_borrador_de_otro(): void
    {
        [, $doctorA] = $this->doctorUser(name: 'Dra. A');
        [$userB] = $this->doctorUser(name: 'Dr. B');
        $consultation = $this->consultation($this->patient(), $doctorA);

        $this->actingAs($userB);
        $this->get(ConsultationResource::getUrl('view', ['record' => $consultation]))->assertOk(); // puede leer la historia
        $this->get(ConsultationResource::getUrl('edit', ['record' => $consultation]))->assertForbidden();
    }

    // ---------- Fórmulas ----------

    public function test_formula_emitida_queda_bloqueada_y_se_puede_anular(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $prescription = $this->prescription($this->patient(), $doctor);
        $prescription->items()->create(['medication' => 'Ácido hialurónico tópico', 'frequency' => 'Cada noche']);
        $this->actingAs($user);

        $prescription->issue();
        $prescription->refresh();
        $this->assertSame(Prescription::STATUS_ISSUED, $prescription->status);
        $this->assertNotNull($prescription->code);

        $this->expectException(LogicException::class);

        try {
            $prescription->items()->first()->update(['frequency' => 'Cada hora']);
        } finally {
            $prescription->annul('Error en la concentración');
            $this->assertSame(Prescription::STATUS_ANNULLED, $prescription->fresh()->status);
        }
    }

    public function test_no_se_emite_una_formula_vacia(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $prescription = $this->prescription($this->patient(), $doctor, ['care_instructions' => null]);
        $this->actingAs($user);

        $this->expectException(LogicException::class);
        $prescription->issue();
    }

    public function test_impresion_de_la_formula_para_el_equipo(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $prescription = $this->prescription($this->patient(), $doctor);
        $prescription->items()->create(['medication' => 'Tretinoína', 'concentration' => '0,025 %']);

        // Borrador: solo su autor.
        [$otherDoctor] = $this->doctorUser(name: 'Dr. Otro');
        $this->actingAs($otherDoctor)->get(route('prescriptions.print', $prescription))->assertForbidden();
        $this->actingAs($user)->get(route('prescriptions.print', $prescription))->assertOk()->assertSee('BORRADOR');

        $prescription->issue();

        $this->actingAs($user)->get(route('prescriptions.print', $prescription))
            ->assertOk()
            ->assertSee('Tretinoína')
            ->assertSee('RM-12345')
            ->assertSee($prescription->fresh()->code)
            ->assertDontSee('BORRADOR');

        // Recepción no imprime fórmulas.
        $reception = User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_RECEPTION]);
        $this->actingAs($reception)->get(route('prescriptions.print', $prescription))->assertForbidden();

        // Ni un médico de otra clínica.
        [$foreignDoctor] = $this->doctorUser($this->otherClinic, 'Dr. Ajeno');
        $this->actingAs($foreignDoctor)->get(route('prescriptions.print', $prescription))->assertNotFound();
    }

    public function test_paginas_del_panel_medico_responden(): void
    {
        [$user, $doctor] = $this->doctorUser();
        $patient = $this->patient();
        $prescription = $this->prescription($patient, $doctor);
        $this->actingAs($user);

        foreach ([
            PatientResource::getUrl('index'),
            PatientResource::getUrl('create'),
            ConsultationResource::getUrl('index'),
            ConsultationResource::getUrl('create'),
            PrescriptionResource::getUrl('index'),
            PrescriptionResource::getUrl('create', ['patient' => $patient->id]),
            PrescriptionResource::getUrl('view', ['record' => $prescription]),
            PrescriptionResource::getUrl('edit', ['record' => $prescription]),
            ServiceRequestResource::getUrl('index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
