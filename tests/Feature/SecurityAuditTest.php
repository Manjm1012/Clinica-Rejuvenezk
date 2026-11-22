<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\PatientResource as AdminPatientResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CallsLivewireOverHttp;
use Tests\TestCase;

/**
 * Auto-auditoría: intentos de cruzar clínicas, escalar rol o filtrar datos clínicos.
 */
class SecurityAuditTest extends TestCase
{
    use CallsLivewireOverHttp, RefreshDatabase;

    protected bool $medicalPanel = true;

    private Clinic $clinic;

    private Clinic $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->other = Clinic::query()->create(['name' => 'Otra', 'slug' => 'otra', 'is_active' => true]);
    }

    private function patient(Clinic $clinic, string $name, string $phone): Patient
    {
        $patient = new Patient(['name' => $name, 'email' => mb_strtolower(str_replace(' ', '', $name)).'@example.com', 'phone' => $phone, 'password' => 'secreta123']);
        $patient->clinic_id = $clinic->id;
        $patient->save();

        return $patient;
    }

    /** @return array{0: User, 1: Doctor} */
    private function doctorUser(Clinic $clinic, string $name = 'Dra. Ruiz'): array
    {
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => User::ROLE_DOCTOR]);
        $doctor = new Doctor(['name' => $name, 'is_active' => true]);
        $doctor->clinic_id = $clinic->id;
        $doctor->save();
        $doctor->user_id = $user->id;
        $doctor->save();

        return [$user, $doctor];
    }

    public function test_recepcion_no_puede_asignar_a_una_cita_un_paciente_de_otra_clinica(): void
    {
        $foreign = $this->patient($this->other, 'Paciente Ajeno', '3009999999');
        $this->actingAs(User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_RECEPTION]));

        $this->livewireCall(AppointmentResource::getUrl('create'), 'create-appointment', [
            'data.patient_id' => (string) $foreign->id,
            'data.patient_name' => 'X',
            'data.patient_phone' => '300',
            'data.appointment_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'data.status' => 'pending',
        ], 'create');

        $this->assertFalse(Appointment::query()->withoutGlobalScopes()->where('patient_id', $foreign->id)->exists());
    }

    public function test_medico_no_puede_vincular_un_servicio_de_otra_clinica_a_su_consulta(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        [$user] = $this->doctorUser($this->clinic);
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');
        $foreignService = new Service(['name' => 'Ajeno', 'slug' => 'ajeno']);
        $foreignService->clinic_id = $this->other->id;
        $foreignService->save();

        $this->actingAs($user);
        $this->livewireCall(ConsultationResource::getUrl('create', ['patient' => $patient->id]), 'create-consultation', [
            'data.reason' => 'Valoración',
            'data.service_id' => (string) $foreignService->id,
        ], 'create');

        $this->assertFalse(Consultation::query()->withoutGlobalScopes()->where('service_id', $foreignService->id)->exists());
    }

    public function test_medico_de_otra_clinica_no_abre_consultas_ni_formulas(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        [, $doctor] = $this->doctorUser($this->clinic);
        [$foreignUser] = $this->doctorUser($this->other, 'Dr. Ajeno');
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');

        $consultation = new Consultation(['consulted_at' => now(), 'reason' => 'Motivo secreto']);
        $consultation->clinic_id = $this->clinic->id;
        $consultation->patient_id = $patient->id;
        $consultation->doctor_id = $doctor->id;
        $consultation->save();

        $prescription = new Prescription(['care_instructions' => 'Indicaciones secretas']);
        $prescription->clinic_id = $this->clinic->id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $doctor->id;
        $prescription->save();

        $this->actingAs($foreignUser);
        $this->get(ConsultationResource::getUrl('view', ['record' => $consultation]))->assertNotFound();
        $this->get(PrescriptionResource::getUrl('view', ['record' => $prescription]))->assertNotFound();
        $this->get(ConsultationResource::getUrl('index'))->assertOk()->assertDontSee('Ana Pérez');
    }

    public function test_el_portal_no_expone_datos_clinicos_ni_notas_internas(): void
    {
        [, $doctor] = $this->doctorUser($this->clinic);
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');
        $patient->update(['allergies' => 'ALERGIA-SECRETA', 'medical_history' => 'ANTECEDENTE-SECRETO']);

        $treatment = new PatientTreatment(['performed_at' => '2026-10-01', 'notes' => 'Nota visible', 'internal_notes' => 'NOTA-INTERNA-SECRETA']);
        $treatment->patient_id = $patient->id;
        $treatment->clinic_id = $this->clinic->id;
        $treatment->save();

        $consultation = new Consultation(['consulted_at' => now(), 'reason' => 'MOTIVO-SECRETO', 'diagnosis' => 'DIAGNOSTICO-SECRETO']);
        $consultation->clinic_id = $this->clinic->id;
        $consultation->patient_id = $patient->id;
        $consultation->doctor_id = $doctor->id;
        $consultation->save();
        $consultation->sign();

        $this->post('/ingresar', ['login' => $patient->email, 'password' => 'secreta123']);

        foreach (['/mi-cuenta', '/mi-cuenta/perfil', '/mi-cuenta/renovar/'.$treatment->id] as $url) {
            $response = $this->get($url);
            foreach (['ALERGIA-SECRETA', 'ANTECEDENTE-SECRETO', 'NOTA-INTERNA-SECRETA', 'MOTIVO-SECRETO', 'DIAGNOSTICO-SECRETO'] as $secret) {
                $response->assertDontSee($secret);
            }
        }

        // Las páginas públicas tampoco.
        $this->get('/')->assertDontSee('ALERGIA-SECRETA')->assertDontSee('NOTA-INTERNA-SECRETA');
    }

    public function test_las_formulas_impresas_no_se_guardan_en_cache(): void
    {
        [$user, $doctor] = $this->doctorUser($this->clinic);
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');
        $prescription = new Prescription(['care_instructions' => 'Protector solar']);
        $prescription->clinic_id = $this->clinic->id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $doctor->id;
        $prescription->save();
        $prescription->issue();

        $staff = $this->actingAs($user)->get(route('prescriptions.print', $prescription))->assertOk();
        $this->assertStringContainsString('no-store', (string) $staff->headers->get('Cache-Control'));

        $this->flushSession();
        $this->post('/ingresar', ['login' => $patient->email, 'password' => 'secreta123']);
        $own = $this->get(route('patient.prescriptions.show', $prescription))->assertOk();
        $this->assertStringContainsString('no-store', (string) $own->headers->get('Cache-Control'));
    }

    public function test_recepcion_no_elimina_pacientes(): void
    {
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');
        $this->actingAs(User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_RECEPTION]));

        $this->get(AdminPatientResource::getUrl('edit', ['record' => $patient]))->assertOk()->assertDontSee('Borrar');
    }

    public function test_el_cifrado_depende_solo_de_app_key(): void
    {
        $patient = $this->patient($this->clinic, 'Ana Pérez', '3001234567');
        $patient->update(['allergies' => 'Lidocaína']);
        $raw = DB::table('patients')->where('id', $patient->id)->value('allergies');

        $this->assertStringNotContainsString('Lidocaína', $raw);
        $this->assertSame('Lidocaína', app('encrypter')->decryptString($raw));

        // Con otra clave no se puede leer.
        $otherKey = new Encrypter(random_bytes(32), config('app.cipher'));
        $this->expectException(DecryptException::class);
        $otherKey->decryptString($raw);
    }

    public function test_el_disco_privado_no_se_sirve_sin_url_firmada(): void
    {
        Storage::disk('local')->put('signatures/firma.png', 'PNG');
        Storage::disk('local')->put('livewire-tmp/subida.jpg', 'JPG');

        $this->get('/storage/signatures/firma.png')->assertForbidden();
        $this->get('/storage/livewire-tmp/subida.jpg')->assertForbidden();
        $this->put('/storage/signatures/otra.png', ['x' => 'y'])->assertStatus(403);
        $this->get('/media/signatures/firma.png')->assertNotFound(); // la ruta pública solo lee el disco public

        Storage::disk('local')->delete(['signatures/firma.png', 'livewire-tmp/subida.jpg']);
    }

    public function test_la_api_publica_no_expone_datos_internos_del_medico(): void
    {
        [, $doctor] = $this->doctorUser($this->clinic);
        $doctor->signature_path = 'signatures/firma.png';
        $doctor->save();

        $json = $this->getJson('/api/home')->assertOk()->json('doctor');

        $this->assertSame('Dra. Ruiz', $json['name']);
        $this->assertArrayNotHasKey('user_id', $json);
        $this->assertArrayNotHasKey('signature_path', $json);
    }
}
