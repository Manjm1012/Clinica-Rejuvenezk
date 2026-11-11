<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Migración de datos 2026_11_10_000100_rename_brand_to_doctor_kevin (SQLite en memoria). */
class BrandRenameMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_renombra_solo_la_marca_anterior_e_invalida_la_cache(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $other = Clinic::query()->create(['name' => 'Clínica Bella Piel', 'slug' => 'bella-piel', 'is_active' => true]);

        DB::table('clinics')->where('id', $clinic->id)->update(['name' => 'Clínica Rejuvenezk']);

        // En SQLite site_settings conserva el único global (group, key): la migración de únicos por
        // clínica se salta SQLite. Por eso la fila de la otra clínica usa otro grupo.
        $rows = [
            ['clinic_id' => $clinic->id, 'group' => 'branding', 'value' => 'Clinica Rejuvenezk'],
            ['clinic_id' => $other->id, 'group' => 'branding_legacy', 'value' => 'Clínica Bella Piel'],
        ];
        foreach ($rows as $row) {
            DB::table('site_settings')->insert($row + ['key' => 'clinic_name', 'type' => 'string', 'label' => 'Nombre', 'created_at' => now(), 'updated_at' => now()]);
        }

        Cache::forever("setting:{$clinic->id}:branding:clinic_name", 'Clinica Rejuvenezk');
        Cache::forever("settings_group:{$clinic->id}:branding", ['clinic_name' => 'Clinica Rejuvenezk']);
        Cache::forever("setting:{$other->id}:branding:clinic_name", 'Clínica Bella Piel');

        (require database_path('migrations/2026_11_10_000100_rename_brand_to_doctor_kevin.php'))->up();

        $this->assertSame('Doctor Kevin Rejuvenezk', $clinic->fresh()->name);
        $this->assertSame('Clínica Bella Piel', $other->fresh()->name);
        $this->assertSame('Doctor Kevin Rejuvenezk', DB::table('site_settings')->where('clinic_id', $clinic->id)->value('value'));
        $this->assertSame('Clínica Bella Piel', DB::table('site_settings')->where('clinic_id', $other->id)->value('value'));

        $this->assertFalse(Cache::has("setting:{$clinic->id}:branding:clinic_name"));
        $this->assertFalse(Cache::has("settings_group:{$clinic->id}:branding"));
        $this->assertTrue(Cache::has("setting:{$other->id}:branding:clinic_name"));
    }
}
