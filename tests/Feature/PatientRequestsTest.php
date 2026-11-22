<?php

namespace Tests\Feature;

use App\Actions\ScheduleServiceRequest;
use App\Filament\Resources\ServiceRequestResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Portal del paciente: solicitar la renovación de un tratamiento ya realizado,
 * ver el estado de la solicitud y las fórmulas emitidas.
 */
class PatientRequestsTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;

    private Patient $ana;

    private Service $toxina;

    private Doctor $doctor;

    private PatientTreatment $treatment;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->ana = $this->makePatient('Ana Pérez', 'ana@example.com', '3001234567');

        $this->toxina = new Service(['name' => 'Toxina botulínica', 'slug' => 'toxina', 'renewal_interval_days' => 180]);
        $this->toxina->clinic_id = $this->clinic->id;
        $this->toxina->save();

        $this->doctor = new Doctor(['name' => 'Dr. Kevin', 'is_active' => true]);
        $this->doctor->clinic_id = $this->clinic->id;
        $this->doctor->save();

        $this->treatment = $this->makeTreatment($this->ana, '2026-04-01'); // vence en octubre: vencido
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makePatient(string $name, string $email, string $phone): Patient
    {
        $patient = new Patient(['name' => $name, 'email' => $email, 'phone' => $phone, 'password' => 'secreta123']);
        $patient->clinic_id = $this->clinic->id;
        $patient->save();

        return $patient;
    }

    private function makeTreatment(Patient $patient, string $performedAt): PatientTreatment
    {
        $treatment = new PatientTreatment(['service_id' => $this->toxina->id, 'doctor_id' => $this->doctor->id, 'performed_at' => $performedAt]);
        $treatment->patient_id = $patient->id;
        $treatment->clinic_id = $patient->clinic_id;
        $treatment->save();

        return $treatment;
    }

    private function loginAs(Patient $patient): void
    {
        $this->post('/ingresar', ['login' => $patient->email, 'password' => 'secreta123'])->assertRedirect();
    }

    public function test_el_paciente_solicita_la_renovacion_de_su_tratamiento(): void
    {
        $this->loginAs($this->ana);

        $this->get('/mi-cuenta')->assertOk()->assertSee(route('patient.requests.create', $this->treatment->id), false);
        $this->get(route('patient.requests.create', $this->treatment->id))->assertOk()->assertSee('Toxina botulínica');

        $this->post(route('patient.requests.store', $this->treatment->id), [
            'preferred_date' => '2026-11-20',
            'preferred_time' => 'tarde',
            'message' => 'Solo frente',
        ])->assertRedirect(route('patient.dashboard'));

        $request = ServiceRequest::query()->withoutGlobalScope('clinic')->firstOrFail();
        $this->assertSame($this->ana->id, $request->patient_id);
        $this->assertSame($this->toxina->id, $request->service_id);
        $this->assertSame($this->doctor->id, $request->doctor_id);
        $this->assertSame($this->clinic->id, $request->clinic_id);
        $this->assertSame('pending', $request->status);

        $this->get('/mi-cuenta')->assertOk()->assertSee('Mis solicitudes')->assertSee('Solicitud enviada');
    }

    public function test_no_se_duplica_una_solicitud_en_curso(): void
    {
        $this->loginAs($this->ana);
        $payload = ['preferred_date' => '2026-11-20', 'preferred_time' => 'manana'];

        $this->post(route('patient.requests.store', $this->treatment->id), $payload);
        $this->post(route('patient.requests.store', $this->treatment->id), $payload);

        $this->assertSame(1, ServiceRequest::query()->withoutGlobalScope('clinic')->count());
        $this->get(route('patient.requests.create', $this->treatment->id))->assertRedirect(route('patient.dashboard'));
    }

    public function test_validacion_de_fecha_y_horario(): void
    {
        $this->loginAs($this->ana);

        $this->post(route('patient.requests.store', $this->treatment->id), ['preferred_date' => '2026-11-09', 'preferred_time' => 'noche'])
            ->assertSessionHasErrors(['preferred_date', 'preferred_time']);

        $this->assertSame(0, ServiceRequest::query()->withoutGlobalScope('clinic')->count());
    }

    public function test_no_puede_solicitar_el_tratamiento_de_otro_paciente(): void
    {
        $luis = $this->makePatient('Luis Díaz', 'luis@example.com', '3007654321');
        $treatmentLuis = $this->makeTreatment($luis, '2026-04-01');

        $this->loginAs($this->ana);

        $this->get(route('patient.requests.create', $treatmentLuis->id))->assertNotFound();
        $this->post(route('patient.requests.store', $treatmentLuis->id), ['preferred_date' => '2026-11-20', 'preferred_time' => 'manana'])->assertNotFound();
    }

    public function test_el_paciente_cancela_su_solicitud_pendiente(): void
    {
        $this->loginAs($this->ana);
        $this->post(route('patient.requests.store', $this->treatment->id), ['preferred_date' => '2026-11-20', 'preferred_time' => 'manana']);
        $request = ServiceRequest::query()->withoutGlobalScope('clinic')->firstOrFail();

        $this->post(route('patient.requests.cancel', $request->id))->assertRedirect(route('patient.dashboard'));

        $this->assertSame('cancelled', $request->fresh()->status);
    }

    public function test_recepcion_agenda_la_solicitud_y_el_paciente_ve_su_cita(): void
    {
        $this->loginAs($this->ana);
        $this->post(route('patient.requests.store', $this->treatment->id), ['preferred_date' => '2026-11-20', 'preferred_time' => 'tarde']);
        $request = ServiceRequest::query()->withoutGlobalScope('clinic')->firstOrFail();

        $reception = User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_RECEPTION]);
        $this->actingAs($reception)->get(ServiceRequestResource::getUrl('index'))->assertOk()->assertSee('Ana Pérez');

        $appointment = app(ScheduleServiceRequest::class)->handle($request, Carbon::parse('2026-11-20 20:00:00'), $this->doctor->id, $reception);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame($this->ana->id, $appointment->patient_id);
        $this->assertSame($this->doctor->id, $appointment->doctor_id);
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('scheduled', $request->fresh()->status);

        $this->loginAs($this->ana);
        $this->get('/mi-cuenta')->assertOk()->assertSee('Agendada')->assertSee('Tu cita');
    }

    public function test_el_paciente_ve_solo_sus_formulas_emitidas(): void
    {
        $issued = $this->makePrescription($this->ana, 'Usar protector solar');
        $issued->issue();
        $draft = $this->makePrescription($this->ana, 'Borrador secreto');

        $luis = $this->makePatient('Luis Díaz', 'luis@example.com', '3007654321');
        $foreign = $this->makePrescription($luis, 'Fórmula de Luis');
        $foreign->issue();

        $this->loginAs($this->ana);

        $this->get('/mi-cuenta')->assertOk()->assertSee('Mis fórmulas e indicaciones')->assertSee($issued->fresh()->code);
        $this->get(route('patient.prescriptions.show', $issued->id))->assertOk()->assertSee('Usar protector solar');
        $this->get(route('patient.prescriptions.show', $draft->id))->assertNotFound();
        $this->get(route('patient.prescriptions.show', $foreign->id))->assertNotFound();
    }

    private function makePrescription(Patient $patient, string $instructions): Prescription
    {
        $prescription = new Prescription(['care_instructions' => $instructions]);
        $prescription->clinic_id = $patient->clinic_id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $this->doctor->id;
        $prescription->save();

        return $prescription;
    }
}
