<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

/**
 * El seeder nunca crea un administrador con clave fija.
 */
class SeederAdminTest extends TestCase
{
    use RefreshDatabase;

    /** Llama al seeder sin `db:seed` (que en producción pide confirmación interactiva). */
    private function runSeeder(): void
    {
        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->run();
    }

    public function test_fuera_de_local_exige_admin_email_y_password(): void
    {
        $this->app['env'] = 'production';
        config(['seeding.admin_email' => null, 'seeding.admin_password' => null]);

        try {
            $this->runSeeder();
            $this->fail('El seeder debió fallar sin ADMIN_EMAIL/ADMIN_PASSWORD.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ADMIN_EMAIL y ADMIN_PASSWORD', $e->getMessage());
        }

        $this->assertSame(0, User::query()->count());
    }

    public function test_fuera_de_local_rechaza_una_clave_corta(): void
    {
        $this->app['env'] = 'production';
        config(['seeding.admin_email' => 'gerencia@clinica.co', 'seeding.admin_password' => 'corta']);

        $this->expectException(RuntimeException::class);
        $this->runSeeder();
    }

    public function test_fuera_de_local_crea_el_admin_con_las_variables_y_no_lo_sobrescribe(): void
    {
        $this->app['env'] = 'production';
        config(['seeding.admin_email' => 'gerencia@clinica.co', 'seeding.admin_password' => 'una-clave-muy-larga-123']);

        $this->runSeeder();

        $admin = User::query()->where('email', 'gerencia@clinica.co')->firstOrFail();
        $this->assertTrue(Hash::check('una-clave-muy-larga-123', $admin->password));
        $this->assertTrue($admin->is_super_admin);

        // Re-ejecutar con otra clave no cambia la del administrador existente.
        config(['seeding.admin_password' => 'otra-clave-distinta-456']);
        $this->runSeeder();
        $this->assertTrue(Hash::check('una-clave-muy-larga-123', $admin->fresh()->password));
    }

    public function test_en_local_sin_variables_genera_una_clave_aleatoria(): void
    {
        config(['seeding.admin_email' => null, 'seeding.admin_password' => null]);

        $this->runSeeder();

        $admin = User::query()->where('email', 'admin@localhost')->firstOrFail();
        $this->assertFalse(Hash::check('password', $admin->password));
        $this->assertFalse(User::query()->where('email', 'admin@rejuvenezk.test')->exists());
    }
}
