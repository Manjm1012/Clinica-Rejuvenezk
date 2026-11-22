<?php

namespace App\Jobs;

use App\Models\Clinic;
use App\Models\Lead;
use App\Services\TayraiService;
use App\Support\CurrentClinic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Envía un lead a TayrAI fuera de la petición del usuario (el formulario ya no espera al CRM).
 * Se despacha con dispatchAfterResponse(): no necesita un worker de colas.
 */
class SyncLeadToTayrai implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $leadId,
        public ?int $clinicId,
    ) {}

    public function handle(): void
    {
        // Fuera de una petición HTTP no hay clínica resuelta: se fija la del lead para leer SUS ajustes.
        app()->instance(CurrentClinic::class, new CurrentClinic($this->clinicId ? Clinic::query()->find($this->clinicId) : null));

        $lead = Lead::query()->withoutGlobalScope('clinic')->with('service')->find($this->leadId);

        if (! $lead || $lead->tayrai_lead_id) {
            return;
        }

        $tayrai = app(TayraiService::class);
        $contactId = $tayrai->upsertContact($lead->name, $lead->phone ?? '', $lead->email ?? '');

        if (! $contactId) {
            return;
        }

        $lead->tayrai_contact_id = $contactId;
        $lead->tayrai_lead_id = $tayrai->createLead($lead);
        $lead->save();
    }
}
