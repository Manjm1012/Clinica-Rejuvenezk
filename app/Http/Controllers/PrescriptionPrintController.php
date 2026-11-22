<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Prescription;
use App\Support\AdminClinic;
use App\Support\Audit;
use App\Support\CurrentClinic;
use App\Support\PublicSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Fórmula imprimible (el navegador la guarda como PDF).
 * La consulta no usa route-model binding: se filtra explícitamente por clínica y paciente.
 */
class PrescriptionPrintController extends Controller
{
    /** Equipo: médicos y administradores de la clínica. Los borradores solo los ve su autor. */
    public function staff(Request $request, int $prescription): Response
    {
        $user = Auth::guard('web')->user();
        $doctor = $user?->activeDoctor();
        abort_unless($user && ($doctor || $user->isClinicAdmin()), 403);

        $record = Prescription::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', AdminClinic::idOrNone())
            ->findOrFail($prescription);

        abort_if($record->isDraft() && (int) $record->doctor_id !== (int) $doctor?->getKey(), 403);

        Audit::log('printed', $record);

        return $this->render($record);
    }

    /** Paciente: solo sus fórmulas emitidas (o anuladas, marcadas como tales). */
    public function patient(Request $request, int $prescription): Response
    {
        $patient = Auth::guard('patient')->user();

        $record = Prescription::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $patient->clinic_id)
            ->where('patient_id', $patient->getKey())
            ->whereIn('status', [Prescription::STATUS_ISSUED, Prescription::STATUS_ANNULLED])
            ->findOrFail($prescription);

        Audit::log('viewed', $record);

        return $this->render($record);
    }

    /** Datos clínicos: el navegador y los proxies no deben guardar copia (no-store). */
    private function render(Prescription $prescription): Response
    {
        $clinic = Clinic::query()->find($prescription->clinic_id);

        // Los ajustes (nombre, contacto) deben ser los de la clínica de la fórmula.
        app()->instance(CurrentClinic::class, new CurrentClinic($clinic));

        $prescription->load([
            'items',
            'patient' => fn ($q) => $q->withoutGlobalScope('clinic'),
            'doctor' => fn ($q) => $q->withoutGlobalScope('clinic'),
        ]);

        return response()->view('prescriptions.print', [
            'prescription' => $prescription,
            'clinic' => $clinic,
            'settings' => PublicSettings::all(),
            'signature' => $this->signatureDataUri($prescription->doctor?->signature_path),
            'logoUrl' => $clinic?->logo_path ? route('media.public', ['path' => ltrim($clinic->logo_path, '/')]) : null,
        ])->withHeaders([
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function signatureDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($path));
    }
}
