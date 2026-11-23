<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientTreatment;
use App\Models\ServiceRequest;
use App\Support\PublicSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * El paciente solicita (y agenda su preferencia de fecha para) la renovación
 * de un tratamiento que ya se hizo en la clínica.
 */
class ServiceRequestController extends Controller
{
    public function create(Request $request, int $treatment): View|RedirectResponse
    {
        $treatment = $this->findTreatment($request, $treatment);

        if ($open = $this->openRequestFor($treatment)) {
            return redirect()->route('patient.dashboard')
                ->with('status', 'Ya tienes una solicitud de '.$treatment->service->name.' en curso ('.mb_strtolower(ServiceRequest::STATUSES[$open->status]).'). Te contactaremos pronto.');
        }

        return view('patient.request-renewal', [
            'settings' => PublicSettings::all(),
            'treatment' => $treatment,
            'times' => ServiceRequest::TIMES,
        ]);
    }

    public function store(Request $request, int $treatment): RedirectResponse
    {
        $treatment = $this->findTreatment($request, $treatment);

        $data = $request->validate([
            'preferred_date' => ['required', 'date', 'after:today', 'before_or_equal:'.now()->addDays(120)->toDateString()],
            'preferred_time' => ['required', Rule::in(array_keys(ServiceRequest::TIMES))],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'preferred_date.required' => 'Elige la fecha en la que te gustaría venir.',
            'preferred_date.after' => 'Elige una fecha a partir de mañana.',
            'preferred_date.before_or_equal' => 'Elige una fecha dentro de los próximos 4 meses.',
            'preferred_time.required' => 'Elige si prefieres en la mañana o en la tarde.',
            'message.max' => 'El mensaje es demasiado largo.',
        ]);

        if ($this->openRequestFor($treatment)) {
            return redirect()->route('patient.dashboard')->with('status', 'Ya tienes una solicitud en curso para este tratamiento.');
        }

        $serviceRequest = new ServiceRequest($data);
        $serviceRequest->clinic_id = $treatment->clinic_id;
        $serviceRequest->patient_id = $treatment->patient_id;
        $serviceRequest->service_id = $treatment->service_id;
        $serviceRequest->patient_treatment_id = $treatment->getKey();
        $serviceRequest->doctor_id = $treatment->doctor_id; // preferencia: el médico que lo atendió
        $serviceRequest->save();

        return redirect()->route('patient.dashboard')
            ->with('status', '¡Listo! Recibimos tu solicitud de '.$treatment->service->name.'. Te confirmaremos la cita por WhatsApp.');
    }

    public function cancel(Request $request, int $serviceRequest): RedirectResponse
    {
        $patient = $request->user('patient');

        $record = ServiceRequest::query()->withoutGlobalScope('clinic')
            ->where('patient_id', $patient->getKey())
            ->where('status', 'pending')
            ->findOrFail($serviceRequest);

        $record->status = 'cancelled';
        $record->staff_notes = trim(($record->staff_notes ?? '')."\nCancelada por el paciente desde el portal.");
        $record->save();

        return redirect()->route('patient.dashboard')->with('status', 'Cancelaste tu solicitud.');
    }

    /** Solo tratamientos propios con servicio (lo que se puede renovar). */
    private function findTreatment(Request $request, int $id): PatientTreatment
    {
        $patient = $request->user('patient');

        return PatientTreatment::query()->withoutGlobalScope('clinic')
            ->select(PatientTreatment::PORTAL_COLUMNS)
            ->addSelect('patient_treatments.clinic_id')
            ->where('patient_id', $patient->getKey())
            ->where('clinic_id', $patient->clinic_id)
            ->whereNotNull('service_id')
            ->with(['service' => fn ($q) => $q->withoutGlobalScope('clinic')->select(['id', 'name', 'renewal_interval_days'])])
            ->findOrFail($id);
    }

    private function openRequestFor(PatientTreatment $treatment): ?ServiceRequest
    {
        return ServiceRequest::query()->withoutGlobalScope('clinic')
            ->where('patient_id', $treatment->patient_id)
            ->where('service_id', $treatment->service_id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->latest('id')
            ->first();
    }
}
