<?php

namespace Tests\Feature;

use App\Filament\Resources\PatientResource;
use App\Filament\Resources\PatientResource\Pages\CreatePatient;
use App\Filament\Resources\PatientResource\Pages\EditPatient;
use App\Filament\Resources\PatientResource\Pages\ListPatients;
use App\Filament\Resources\PatientResource\RelationManagers\TreatmentsRelationManager;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Widgets\RenewalsWidget;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Service;
use App\Models\User;
use App\Support\PatientCsvExport;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/** Gestión de pacientes en Filament (SQLite en memoria). */
class PatientAdminTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;

    private Clinic $otherClinic;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00', 'America/Bogota'));

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->otherClinic = Clinic::query()->create(['name' => 'Otra clínica', 'slug' => 'otra', 'is_active' => true]);

        $this->actingAs(User::factory()->create(['clinic_id' => $this->clinic->id]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makePatient(Clinic $clinic, array $attributes = []): Patient
    {
        static $n = 0;
        $n++;

        $patient = new Patient(array_merge([
            'name' => "Paciente {$n}",
            'email' => "p{$n}@example.com",
            'phone' => '30000000'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
        ], $attributes));
        $patient->clinic_id = $clinic->id;
        $patient->save();

        return $patient;
    }

    private function makeTreatment(Patient $patient, array $attributes = []): PatientTreatment
    {
        $treatment = new PatientTreatment(array_merge(['patient_id' => $patient->id, 'performed_at' => '2026-10-01'], $attributes));
        $treatment->clinic_id = $patient->clinic_id;
        $treatment->save();

        return $treatment;
    }

    public function test_lista_de_pacientes_solo_muestra_la_clinica_del_usuario(): void
    {
        $mine = $this->makePatient($this->clinic, ['name' => 'Ana Propia']);
        $foreign = $this->makePatient($this->otherClinic, ['name' => 'Bea Ajena']);

        Livewire::test(ListPatients::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$foreign]);

        // Tampoco se puede abrir por URL un paciente de otra clínica.
        $this->get(PatientResource::getUrl('edit', ['record' => $foreign]))->assertNotFound();
        $this->get(PatientResource::getUrl('edit', ['record' => $mine]))->assertOk();
    }

    public function test_crear_paciente_asigna_la_clinica_del_usuario_y_normaliza(): void
    {
        Livewire::test(CreatePatient::class)
            ->fillForm(['name' => 'Carla Ruiz', 'phone' => '+57 311 222 3344', 'email' => 'CARLA@Example.com'])
            ->call('create')
            ->assertHasNoFormErrors();

        $patient = Patient::query()->where('name', 'Carla Ruiz')->firstOrFail();
        $this->assertSame($this->clinic->id, $patient->clinic_id);
        $this->assertSame('3112223344', $patient->phone);
        $this->assertSame('carla@example.com', $patient->email);
        $this->assertNull($patient->password);
    }

    public function test_no_permite_celular_duplicado_en_la_misma_clinica(): void
    {
        $this->makePatient($this->clinic, ['phone' => '3112223344']);

        Livewire::test(CreatePatient::class)
            ->fillForm(['name' => 'Duplicada', 'phone' => '311 222 3344'])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    public function test_csv_incluye_solo_pacientes_con_consentimiento_y_sin_datos_internos(): void
    {
        $withConsent = $this->makePatient($this->clinic, ['name' => 'Con Permiso', 'marketing_consent' => true]);
        $this->makePatient($this->clinic, ['name' => 'Sin Permiso', 'marketing_consent' => false]);
        $this->makePatient($this->otherClinic, ['name' => 'Otra Clinica', 'marketing_consent' => true]);
        $this->makeTreatment($withConsent, ['internal_notes' => 'NOTA-INTERNA-SECRETA']);

        $csv = PatientCsvExport::csv($this->clinic->id);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Nombre;Teléfono;Correo;"Fecha de consentimiento"', $csv);
        $this->assertStringContainsString('Con Permiso', $csv);
        $this->assertStringNotContainsString('Sin Permiso', $csv);
        $this->assertStringNotContainsString('Otra Clinica', $csv);
        $this->assertStringNotContainsString('NOTA-INTERNA-SECRETA', $csv);
        $this->assertStringNotContainsString('internal_notes', $csv);
        $this->assertStringNotContainsString('document', $csv);
        $this->assertCount(2, array_filter(explode("\n", trim($csv)))); // encabezado + 1 fila

        Livewire::test(ListPatients::class)
            ->callAction('exportCsv')
            ->assertFileDownloaded('pacientes-con-consentimiento-2026-11-10.csv');
    }

    public function test_csv_neutraliza_formulas_de_excel(): void
    {
        $this->makePatient($this->clinic, ['name' => '=HYPERLINK("x")', 'marketing_consent' => true]);

        $this->assertStringContainsString("'=HYPERLINK", PatientCsvExport::csv($this->clinic->id));
    }

    public function test_widget_muestra_proximas_y_vencidas_y_excluye_no_aplica(): void
    {
        $service = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Toxina', 'slug' => 'toxina', 'renewal_interval_days' => 180]);
        $sinIntervalo = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Limpieza', 'slug' => 'limpieza']);
        $ana = $this->makePatient($this->clinic);
        $bea = $this->makePatient($this->clinic);
        $foreign = $this->makePatient($this->otherClinic);

        $vencida = $this->makeTreatment($ana, ['next_due_at' => '2026-11-01']);
        $proxima = $this->makeTreatment($bea, ['next_due_at' => '2026-11-20']);
        $vigente = $this->makeTreatment($bea, ['next_due_at' => '2027-03-01']);
        $noAplica = $this->makeTreatment($ana, ['service_id' => $sinIntervalo->id]);
        $otraClinica = $this->makeTreatment($foreign, ['next_due_at' => '2026-11-01']);

        // Toxina repetida: la aplicación antigua (vencida) queda reemplazada por la nueva (vigente).
        $toxinaVieja = $this->makeTreatment($ana, ['service_id' => $service->id, 'performed_at' => '2026-01-01']);
        $toxinaNueva = $this->makeTreatment($ana, ['service_id' => $service->id, 'performed_at' => '2026-11-01']);

        $this->assertSame('no_aplica', $noAplica->fresh()->renewal_status);
        $this->assertSame('vencido', $toxinaVieja->fresh()->renewal_status);

        Livewire::test(RenewalsWidget::class)
            ->assertCanSeeTableRecords([$vencida, $proxima], inOrder: true)
            ->assertCanNotSeeTableRecords([$vigente, $noAplica, $otraClinica, $toxinaVieja, $toxinaNueva]);
    }

    public function test_primer_tratamiento_convierte_el_lead_desde_filament(): void
    {
        $lead = Lead::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Ana', 'phone' => '3000000000', 'source' => 'website', 'status' => 'contacted']);
        $patient = $this->makePatient($this->clinic);
        $patient->lead_id = $lead->id;
        $patient->save();
        $service = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Toxina', 'slug' => 'toxina']);

        Livewire::test(TreatmentsRelationManager::class, ['ownerRecord' => $patient, 'pageClass' => EditPatient::class])
            ->callTableAction('create', data: [
                'service_id' => $service->id,
                'performed_at' => '2026-11-05',
                'notes' => 'Visible',
                'internal_notes' => 'Interna',
            ])
            ->assertHasNoTableActionErrors();

        $treatment = PatientTreatment::query()->firstOrFail();
        $this->assertSame($this->clinic->id, $treatment->clinic_id);
        $this->assertSame($patient->id, $treatment->patient_id);
        $this->assertSame('converted', $lead->fresh()->status);
    }

    public function test_el_lead_solo_cambia_con_el_primer_tratamiento(): void
    {
        $lead = Lead::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Ana', 'phone' => '3000000000', 'source' => 'website', 'status' => 'new']);
        $patient = $this->makePatient($this->clinic);
        $patient->lead_id = $lead->id;
        $patient->save();

        $this->makeTreatment($patient);
        $lead->update(['status' => 'lost']); // la clínica lo cambia a mano después
        $this->makeTreatment($patient);

        $this->assertSame('lost', $lead->fresh()->status);
    }

    public function test_service_resource_tiene_el_campo_de_renovacion(): void
    {
        $service = Service::query()->create(['clinic_id' => $this->clinic->id, 'name' => 'Toxina', 'slug' => 'toxina']);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->assertFormFieldExists('renewal_interval_days')
            ->fillForm(['renewal_interval_days' => 180])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(180, $service->fresh()->renewal_interval_days);
    }
}
