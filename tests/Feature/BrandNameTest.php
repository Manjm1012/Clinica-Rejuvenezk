<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Marca por defecto "Dr. Kevin Rejuvenezk" en el sitio, SEO, paneles y fórmula impresa.
 */
class BrandNameTest extends TestCase
{
    use RefreshDatabase;

    protected bool $medicalPanel = true;

    private const BRAND = 'Dr. Kevin Rejuvenezk';

    public function test_sitio_publico_seo_y_datos_estructurados(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>'.self::BRAND.' | ', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="'.self::BRAND.'">', $html);
        $this->assertStringContainsString('"@type":"MedicalClinic","name":"'.self::BRAND.'"', $html);
        $this->assertStringContainsString('<p>'.self::BRAND.'</p>', $html); // pie de página
        $this->assertStringContainsString('<span class="brand-monogram">DK</span>', $html); // sin logo: monograma
        $this->assertStringNotContainsString('Doctor Kevin Rejuvenezk', $html);

        foreach (['/quienes-somos', '/resultados', '/equipo-medico', '/preguntas-frecuentes', '/servicios', '/ingresar', '/registro', '/politica-de-datos'] as $url) {
            $this->get($url)->assertOk()->assertSee(self::BRAND)->assertDontSee('Doctor Kevin Rejuvenezk');
        }
    }

    public function test_el_monograma_de_la_marca_es_dk(): void
    {
        $this->assertSame('DK', Media::initials(self::BRAND));
    }

    public function test_paneles_filament(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee(self::BRAND)->assertDontSee('Doctor Kevin Rejuvenezk');
        $this->get('/medico/login')->assertOk()->assertSee(self::BRAND.' · Panel médico');
    }

    public function test_encabezado_de_la_formula_impresa(): void
    {
        $clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => User::ROLE_DOCTOR]);
        $doctor = new Doctor(['name' => 'Dra. Ruiz', 'is_active' => true]);
        $doctor->clinic_id = $clinic->id;
        $doctor->save();
        $doctor->user_id = $user->id;
        $doctor->save();

        $patient = new Patient(['name' => 'Ana', 'phone' => '3001234567']);
        $patient->clinic_id = $clinic->id;
        $patient->save();

        $prescription = new Prescription(['care_instructions' => 'Protector solar']);
        $prescription->clinic_id = $clinic->id;
        $prescription->patient_id = $patient->id;
        $prescription->doctor_id = $doctor->id;
        $prescription->save();
        $prescription->issue();

        $this->actingAs($user)->get(route('prescriptions.print', $prescription))
            ->assertOk()
            ->assertSee('<h1>'.self::BRAND.'</h1>', false)
            ->assertSee('Documento generado por '.self::BRAND);
    }
}
