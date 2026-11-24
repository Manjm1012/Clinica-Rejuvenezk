<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientTreatment;
use App\Models\Prescription;
use App\Models\ServiceRequest;
use App\Support\MedicalPanel;
use App\Support\PublicSettings;
use App\Support\Renewals;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $patient = $request->user('patient');
        $settings = PublicSettings::all();
        $hasWhatsapp = PublicSettings::whatsappLink($settings, '') !== null;

        // Columnas explícitas: internal_notes nunca sale en una consulta del portal.
        $treatments = $patient->treatments()
            ->select(PatientTreatment::PORTAL_COLUMNS)
            ->with([
                'service:id,name,slug,whatsapp_text,renewal_interval_days',
                'doctor:id,name',
            ])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->get();

        // Solo el último tratamiento de cada servicio cuenta para renovar; los anteriores quedan "Renovado".
        $currentIds = Renewals::currentIds($treatments);
        $current = $treatments->filter(fn (PatientTreatment $t) => in_array($t->id, $currentIds, true));

        $renewUrls = $current
            ->filter(fn (PatientTreatment $t) => in_array($t->renewal_status, ['proxima', 'vencido'], true))
            ->mapWithKeys(function (PatientTreatment $t) use ($settings, $hasWhatsapp) {
                if (! $hasWhatsapp) {
                    return [$t->id => route('contact', array_filter(['servicio' => $t->service?->slug])).'#formulario'];
                }

                $url = $t->service
                    ? $t->service->whatsappUrlWithText('Hola, quiero agendar la renovación de '.$t->service->name)
                    : PublicSettings::whatsappLink($settings, 'Hola, quiero agendar la renovación de mi tratamiento');

                return [$t->id => $url];
            });

        $nextRenewal = $current
            ->filter(fn (PatientTreatment $t) => $t->due_date !== null)
            ->sortBy(fn (PatientTreatment $t) => $t->due_date->toDateString())
            ->first();

        $requests = ServiceRequest::query()->withoutGlobalScope('clinic')
            ->where('patient_id', $patient->getKey())
            ->with([
                'service' => fn ($q) => $q->withoutGlobalScope('clinic')->select(['id', 'name']),
                'appointment' => fn ($q) => $q->withoutGlobalScope('clinic')->select(['id', 'appointment_at']),
            ])
            ->latest('id')
            ->limit(10)
            ->get();

        // Servicios con una solicitud en curso: el botón de renovar muestra "Solicitud enviada".
        $openServiceIds = $requests->filter->isOpen()->pluck('service_id')->filter()->unique()->all();

        // Solo fórmulas emitidas o anuladas (los borradores del médico nunca llegan al portal).
        $prescriptions = ! MedicalPanel::enabled() ? collect() : Prescription::query()->withoutGlobalScope('clinic')
            ->where('patient_id', $patient->getKey())
            ->whereIn('status', [Prescription::STATUS_ISSUED, Prescription::STATUS_ANNULLED])
            ->select(['id', 'patient_id', 'doctor_id', 'code', 'status', 'issued_at', 'valid_until'])
            ->with(['doctor' => fn ($q) => $q->withoutGlobalScope('clinic')->select(['id', 'name'])])
            ->latest('issued_at')
            ->get();

        return view('patient.dashboard', [
            'requests' => $requests,
            'openServiceIds' => $openServiceIds,
            'prescriptions' => $prescriptions,
            'settings' => $settings,
            'patient' => $patient,
            'treatments' => $treatments,
            'currentIds' => $currentIds,
            'nextRenewal' => $nextRenewal,
            'renewUrls' => $renewUrls,
            'hasWhatsapp' => $hasWhatsapp,
        ]);
    }
}
