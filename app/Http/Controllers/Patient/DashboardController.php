<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientTreatment;
use App\Support\PublicSettings;
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

        $renewUrls = $treatments
            ->filter(fn (PatientTreatment $t) => in_array($t->renewal_status, ['proxima', 'vencido'], true))
            ->mapWithKeys(function (PatientTreatment $t) use ($settings, $hasWhatsapp) {
                if (! $hasWhatsapp) {
                    return [$t->id => route('home').'#formulario'];
                }

                $url = $t->service
                    ? $t->service->whatsappUrlWithText('Hola, quiero agendar la renovación de '.$t->service->name)
                    : PublicSettings::whatsappLink($settings, 'Hola, quiero agendar la renovación de mi tratamiento');

                return [$t->id => $url];
            });

        $nextRenewal = $treatments
            ->filter(fn (PatientTreatment $t) => $t->due_date !== null)
            ->sortBy(fn (PatientTreatment $t) => $t->due_date->timestamp)
            ->first();

        return view('patient.dashboard', [
            'settings' => $settings,
            'patient' => $patient,
            'treatments' => $treatments,
            'nextRenewal' => $nextRenewal,
            'renewUrls' => $renewUrls,
            'hasWhatsapp' => $hasWhatsapp,
        ]);
    }
}
