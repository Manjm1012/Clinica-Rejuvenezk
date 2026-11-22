<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Migraciones nuevas sobre datos existentes: roles de los usuarios previos y rollbacks seguros.
 */
class MigrationSafetyTest extends TestCase
{
    use RefreshDatabase;

    /** Lote nuevo, en orden de aplicación. */
    private const NEW_MIGRATIONS = [
        '2026_11_22_000100_add_roles_and_doctor_accounts',
        '2026_11_22_000200_create_clinical_records_tables',
        '2026_11_22_000300_create_service_requests_and_audit_logs',
    ];

    private function migration(string $name): object
    {
        return require database_path("migrations/{$name}.php");
    }

    private function downNew(): void
    {
        foreach (array_reverse(self::NEW_MIGRATIONS) as $name) {
            $this->migration($name)->down();
        }
    }

    private function upNew(): void
    {
        foreach (self::NEW_MIGRATIONS as $name) {
            $this->migration($name)->up();
        }
    }

    public function test_los_usuarios_existentes_quedan_como_administradores_y_entran_al_panel(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();

        // Estado de producción: aún no existe users.role.
        $this->downNew();
        $this->assertFalse(Schema::hasColumn('users', 'role'));

        $id = DB::table('users')->insertGetId([
            'name' => 'Administrador existente', 'email' => 'existente@clinica.co', 'password' => bcrypt('clave-larga-123'),
            'clinic_id' => $clinic->id, 'is_super_admin' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->upNew();

        $user = User::query()->findOrFail($id);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertTrue($user->isClinicAdmin());
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_bajar_y_volver_a_subir_el_lote_con_tablas_vacias(): void
    {
        $this->downNew();
        foreach (['consultations', 'prescriptions', 'service_requests', 'audit_logs'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }

        $this->upNew();
        foreach (['consultations', 'prescriptions', 'service_requests', 'audit_logs'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        $this->assertTrue(Schema::hasColumn('users', 'role'));
    }

    public function test_el_rollback_se_niega_si_hay_historia_clinica(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $doctor = new Doctor(['name' => 'Dra. Ruiz', 'is_active' => true]);
        $doctor->clinic_id = $clinic->id;
        $doctor->save();
        $patient = new Patient(['name' => 'Ana', 'phone' => '3001234567', 'allergies' => 'Lidocaína']);
        $patient->clinic_id = $clinic->id;
        $patient->save();
        $consultation = new Consultation(['consulted_at' => now(), 'reason' => 'Valoración']);
        $consultation->clinic_id = $clinic->id;
        $consultation->patient_id = $patient->id;
        $consultation->doctor_id = $doctor->id;
        $consultation->save();

        foreach ([
            '2026_11_22_000200_create_clinical_records_tables' => 'consultations',
            '2026_11_22_000100_add_roles_and_doctor_accounts' => 'patients',
            '2026_11_10_000200_create_patients_tables' => 'patients',
        ] as $migration => $table) {
            try {
                $this->migration($migration)->down();
                $this->fail("{$migration} debió negarse a borrar datos.");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString("la tabla {$table} tiene datos de pacientes", $e->getMessage());
            }
        }

        $this->assertSame(1, Consultation::query()->withoutGlobalScopes()->count());
        $this->assertSame('Lidocaína', $patient->fresh()->allergies);
    }
}
