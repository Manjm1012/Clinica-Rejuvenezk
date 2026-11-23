<?php

namespace App\Support;

use App\Models\Patient;
use App\Models\PatientTreatment;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exportación de pacientes para campañas: SOLO quienes dieron consentimiento de marketing.
 * Columnas mínimas (sin notas internas ni datos clínicos).
 */
class PatientCsvExport
{
    public const HEADERS = ['Nombre', 'Teléfono', 'Correo', 'Fecha de consentimiento'];

    /** @return array<int, array<int, string>> */
    public static function rows(int $clinicId): array
    {
        $timezone = PatientTreatment::timezoneForClinic($clinicId);

        return Patient::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->where('marketing_consent', true)
            ->orderBy('name')
            ->get(['name', 'phone', 'email', 'marketing_consent_at'])
            ->map(fn (Patient $patient) => [
                static::safe($patient->name),
                static::safe($patient->phone),
                static::safe($patient->email),
                // Hora local de la clínica (la app guarda en UTC).
                $patient->marketing_consent_at?->copy()->setTimezone($timezone)->format('Y-m-d H:i') ?? '',
            ])
            ->all();
    }

    /** CSV con BOM UTF-8 y separador ";" (Excel en español lo abre en columnas). */
    public static function csv(int $clinicId): string
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, static::HEADERS, ';');

        foreach (static::rows($clinicId) as $row) {
            fputcsv($handle, $row, ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public static function download(int $clinicId): StreamedResponse
    {
        $filename = 'pacientes-con-consentimiento-'.PatientTreatment::todayForClinic($clinicId).'.csv';

        return response()->streamDownload(function () use ($clinicId) {
            echo static::csv($clinicId);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita inyección de fórmulas en Excel (=, +, -, @ al inicio). */
    private static function safe(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) ? "'".$value : $value;
    }
}
