@php
    $clinicName = $settings['clinic_name'] ?? $clinic?->name ?? 'Clínica';
    $patient = $prescription->patient;
    $doctor = $prescription->doctor;
    $timezone = \App\Models\PatientTreatment::timezoneForClinic($prescription->clinic_id);
    $issuedAt = ($prescription->issued_at ?? $prescription->created_at)?->copy()->setTimezone($timezone);
    $isAnnulled = $prescription->status === \App\Models\Prescription::STATUS_ANNULLED;
    $isDraft = $prescription->isDraft();
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Fórmula {{ $prescription->code ?? 'borrador' }} · {{ $clinicName }}</title>
    <style>
        :root { --ink: #1f2a1d; --muted: #5d6b59; --line: #d9e1d6; --accent: #6f8f69; --bg: #f4f6f2; }
        * { box-sizing: border-box; }
        body { margin: 0; font: 14px/1.5 "Segoe UI", system-ui, -apple-system, sans-serif; color: var(--ink); background: var(--bg); }
        .toolbar { max-width: 820px; margin: 16px auto; padding: 0 16px; display: flex; gap: 8px; justify-content: flex-end; }
        .toolbar button { font: inherit; border: 0; border-radius: 999px; padding: 10px 18px; cursor: pointer; background: var(--accent); color: #fff; }
        .sheet { position: relative; max-width: 820px; margin: 0 auto 32px; background: #fff; padding: 40px 48px; border-radius: 12px; box-shadow: 0 10px 30px rgba(31,42,29,.08); overflow: hidden; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; border-bottom: 2px solid var(--accent); padding-bottom: 16px; }
        header img { max-height: 64px; max-width: 200px; object-fit: contain; }
        .clinic h1 { margin: 0; font-size: 20px; }
        .clinic p, .meta p { margin: 2px 0; color: var(--muted); font-size: 12px; }
        .meta { text-align: right; }
        .meta strong { font-size: 15px; color: var(--ink); }
        h2 { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: var(--accent); margin: 24px 0 8px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px 24px; }
        .grid div span { display: block; font-size: 11px; color: var(--muted); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { font-size: 11px; color: var(--muted); font-weight: 600; }
        td small { display: block; color: var(--muted); }
        .instructions { white-space: pre-line; background: var(--bg); border-radius: 8px; padding: 14px 16px; }
        .signature { margin-top: 48px; display: flex; justify-content: flex-end; }
        .signature div { text-align: center; min-width: 260px; }
        .signature img { max-height: 70px; display: block; margin: 0 auto 4px; }
        .signature .line { border-top: 1px solid var(--ink); padding-top: 6px; }
        footer { margin-top: 32px; font-size: 11px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 10px; }
        .stamp { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-24deg); font-size: 88px; font-weight: 800; letter-spacing: .1em; opacity: .12; pointer-events: none; white-space: nowrap; }
        .stamp.annulled { color: #b42318; opacity: .18; }
        @media (max-width: 640px) { .sheet { padding: 24px 16px; } .grid { grid-template-columns: 1fr 1fr; } header { flex-direction: column; } .meta { text-align: left; } }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; margin: 0; max-width: none; padding: 0; border-radius: 0; }
            @page { margin: 16mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button type="button" onclick="window.print()">Imprimir o guardar PDF</button>
</div>

<main class="sheet">
    @if ($isAnnulled)
        <div class="stamp annulled">ANULADA</div>
    @elseif ($isDraft)
        <div class="stamp">BORRADOR</div>
    @endif

    <header>
        <div class="clinic">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $clinicName }}">
            @endif
            <h1>{{ $clinicName }}</h1>
            @if (! empty($settings['address']))<p>{{ $settings['address'] }}</p>@endif
            @if (! empty($settings['phone']) || ! empty($settings['email']))
                <p>{{ collect([$settings['phone'] ?? null, $settings['email'] ?? null])->filter()->implode(' · ') }}</p>
            @endif
        </div>
        <div class="meta">
            <p>Fórmula médica</p>
            <strong>{{ $prescription->code ?? 'Sin número (borrador)' }}</strong>
            <p>Fecha: {{ $issuedAt?->format('d/m/Y H:i') }}</p>
            @if ($prescription->valid_until)<p>Válida hasta: {{ $prescription->valid_until->format('d/m/Y') }}</p>@endif
        </div>
    </header>

    <h2>Paciente</h2>
    <div class="grid">
        <div><span>Nombre</span>{{ $patient?->name }}</div>
        <div><span>Documento</span>{{ trim(($patient?->document_type ?? '').' '.($patient?->document_number ?? '')) ?: '—' }}</div>
        <div><span>Edad</span>{{ $patient?->birth_date ? $patient->birth_date->age.' años' : '—' }}</div>
    </div>

    @if ($prescription->diagnosis)
        <h2>Diagnóstico</h2>
        <p>{{ $prescription->diagnosis }}</p>
    @endif

    @if ($prescription->items->isNotEmpty())
        <h2>Medicamentos y productos</h2>
        <table>
            <thead>
            <tr><th>#</th><th>Medicamento</th><th>Dosis y vía</th><th>Frecuencia y duración</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
            @foreach ($prescription->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->medication }}</strong>
                        <small>{{ collect([$item->concentration, $item->dosage_form])->filter()->implode(' · ') }}</small>
                        @if ($item->instructions)<small>{{ $item->instructions }}</small>@endif
                    </td>
                    <td>{{ collect([$item->dose, $item->route])->filter()->implode(' · ') ?: '—' }}</td>
                    <td>{{ collect([$item->frequency, $item->duration])->filter()->implode(' · ') ?: '—' }}</td>
                    <td>{{ $item->quantity ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if ($prescription->care_instructions)
        <h2>Indicaciones y cuidados posteriores</h2>
        <div class="instructions">{{ $prescription->care_instructions }}</div>
    @endif

    <div class="signature">
        <div>
            @if ($signature)
                <img src="{{ $signature }}" alt="Firma">
            @endif
            <div class="line">
                <strong>{{ $doctor?->name }}</strong><br>
                @if ($doctor?->specialty){{ $doctor->specialty }}<br>@endif
                @if ($doctor?->professional_registration)Registro profesional: {{ $doctor->professional_registration }}@endif
            </div>
        </div>
    </div>

    <footer>
        @if ($isAnnulled)
            <strong>Fórmula anulada el {{ $prescription->annulled_at?->copy()->setTimezone($timezone)->format('d/m/Y') }}.</strong>
            Motivo: {{ $prescription->annulled_reason }}<br>
        @endif
        Documento generado por {{ $clinicName }}. Ante cualquier reacción adversa suspenda el producto y comuníquese con la clínica.
    </footer>
</main>
</body>
</html>
