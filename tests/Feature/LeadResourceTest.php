<?php

namespace Tests\Feature;

use App\Filament\Resources\LeadResource\Pages\CreateLead;
use App\Filament\Resources\LeadResource\Pages\EditLead;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['clinic_id' => 1]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_source_tiene_website_por_defecto(): void
    {
        Livewire::test(CreateLead::class)->assertFormSet(['source' => 'website']);
    }

    public function test_crear_lead_con_source_vacio_guarda_website(): void
    {
        Livewire::test(CreateLead::class)
            ->fillForm(['name' => 'Lead sin origen', 'phone' => '3001112233', 'status' => 'new', 'source' => ''])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('website', Lead::query()->where('name', 'Lead sin origen')->value('source'));
    }

    public function test_editar_lead_y_vaciar_source_no_falla(): void
    {
        $lead = Lead::query()->create(['clinic_id' => 1, 'name' => 'Lead', 'phone' => '300', 'source' => 'instagram']);

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['source' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('website', $lead->fresh()->source);
    }
}
