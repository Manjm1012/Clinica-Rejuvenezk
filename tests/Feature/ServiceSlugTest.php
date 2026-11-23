<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['clinic_id' => 1]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_al_crear_el_slug_se_genera_desde_el_nombre(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm(['name' => 'Peeling Químico'])
            ->assertFormSet(['slug' => 'peeling-quimico']);
    }

    public function test_al_editar_el_nombre_no_se_reescribe_el_slug(): void
    {
        $service = Service::query()->create(['clinic_id' => 1, 'name' => 'Toxina', 'slug' => 'toxina-botulinica']);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['name' => 'Toxina botulínica premium'])
            ->assertFormSet(['slug' => 'toxina-botulinica'])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();
        $this->assertSame('Toxina botulínica premium', $service->name);
        $this->assertSame('toxina-botulinica', $service->slug);
    }

    public function test_al_editar_el_slug_sigue_siendo_editable_a_mano(): void
    {
        $service = Service::query()->create(['clinic_id' => 1, 'name' => 'Toxina', 'slug' => 'toxina']);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['slug' => 'toxina-botulinica'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('toxina-botulinica', $service->fresh()->slug);
    }
}
