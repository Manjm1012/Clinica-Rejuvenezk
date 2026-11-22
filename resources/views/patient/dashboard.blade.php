@php
    $fmtDate = fn ($date) => $date->copy()->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $firstName = \Illuminate\Support\Str::of($patient->name)->trim()->explode(' ')->first();
    $labels = \App\Models\PatientTreatment::RENEWAL_LABELS;
    $requestStatuses = \App\Models\ServiceRequest::STATUSES;
    $times = \App\Models\ServiceRequest::TIMES;
    // Botón de renovación: formulario interno si el tratamiento tiene servicio; si no, WhatsApp/formulario.
    $renewTarget = fn ($t) => $t->service_id ? route('patient.requests.create', $t->id) : ($renewUrls[$t->id] ?? null);
@endphp

<x-layouts.site :settings="$settings" :title="'Mi cuenta | ' . $clinicName">
    <main class="dk-portal dk-portal-wide">
        <header class="dk-dash-head">
            <div>
                <p class="kicker dk-portal-kicker">Mi cuenta</p>
                <h1 class="dk-portal-title">Hola, {{ $firstName }}</h1>
            </div>
            <div class="dk-dash-actions">
                <a href="{{ route('patient.profile') }}" class="dk-btn-outline">Mi perfil</a>
                <form method="POST" action="{{ route('patient.logout') }}">
                    @csrf
                    <button type="submit" class="dk-btn-link">Cerrar sesión</button>
                </form>
            </div>
        </header>

        @if (session('status'))
            <div class="dk-alert dk-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($nextRenewal)
            @php($nextStatus = $nextRenewal->renewal_status)
            @php($nextOpen = in_array($nextRenewal->service_id, $openServiceIds, true))
            <section class="dk-next-card dk-next-card--{{ $nextStatus }}" aria-labelledby="dk-next-title">
                <div>
                    <h2 id="dk-next-title">Próxima renovación</h2>
                    <p class="dk-next-name">{{ $nextRenewal->service?->name ?? 'Tratamiento' }}</p>
                    <p class="dk-next-date">
                        @if ($nextStatus === 'vencido')
                            Venció el {{ $fmtDate($nextRenewal->due_date) }}
                        @else
                            {{ $fmtDate($nextRenewal->due_date) }}
                        @endif
                    </p>
                </div>
                @if ($nextOpen)
                    <span class="dk-badge dk-badge--vigente">Solicitud enviada</span>
                @elseif (isset($renewUrls[$nextRenewal->id]))
                    <a href="{{ $renewTarget($nextRenewal) }}" class="dk-btn-primary">Agendar renovación</a>
                @endif
            </section>
        @endif

        @if ($requests->isNotEmpty())
            <section class="dk-history" aria-labelledby="dk-requests-title">
                <h2 id="dk-requests-title" class="dk-section-title">Mis solicitudes</h2>

                @foreach ($requests as $serviceRequest)
                    <article class="dk-treatment">
                        <div class="dk-treatment-main">
                            <h3>Renovación de {{ $serviceRequest->service?->name ?? 'tratamiento' }}</h3>
                            <p class="dk-treatment-meta">
                                Enviada el {{ $fmtDate($serviceRequest->created_at) }}
                                @if ($serviceRequest->preferred_date)
                                    · Prefieres el {{ $fmtDate($serviceRequest->preferred_date) }}{{ $serviceRequest->preferred_time ? ' ('.mb_strtolower(strtok($times[$serviceRequest->preferred_time] ?? '', ' ')).')' : '' }}
                                @endif
                            </p>
                            @if ($serviceRequest->status === 'scheduled' && $serviceRequest->appointment)
                                <p class="dk-treatment-due">
                                    Tu cita: {{ $serviceRequest->appointment->appointment_at->copy()->setTimezone(\App\Models\PatientTreatment::timezoneForClinic($patient->clinic_id))->locale('es')->translatedFormat('l j \d\e F, g:i a') }}
                                </p>
                            @endif
                        </div>
                        <div class="dk-treatment-side">
                            <span class="dk-badge dk-badge--{{ match ($serviceRequest->status) { 'pending' => 'proxima', 'scheduled' => 'vigente', default => 'no_aplica' } }}">
                                {{ $requestStatuses[$serviceRequest->status] ?? $serviceRequest->status }}
                            </span>
                            @if ($serviceRequest->status === 'pending')
                                <form method="POST" action="{{ route('patient.requests.cancel', $serviceRequest->id) }}"
                                      onsubmit="return confirm('¿Cancelar esta solicitud?')">
                                    @csrf
                                    <button type="submit" class="dk-btn-link">Cancelar</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="dk-history" aria-labelledby="dk-history-title">
            <h2 id="dk-history-title" class="dk-section-title">Mis procedimientos</h2>

            @forelse ($treatments as $treatment)
                @php($isCurrent = in_array($treatment->id, $currentIds, true))
                @php($status = $treatment->renewal_status)
                @php($hasOpen = $treatment->service_id && in_array($treatment->service_id, $openServiceIds, true))
                <article class="dk-treatment">
                    <div class="dk-treatment-main">
                        <h3>{{ $treatment->service?->name ?? 'Tratamiento' }}</h3>
                        <p class="dk-treatment-meta">
                            <time datetime="{{ $treatment->performed_at->toDateString() }}">{{ $fmtDate($treatment->performed_at) }}</time>
                            @if ($treatment->doctor)
                                · {{ $treatment->doctor->name }}
                            @endif
                        </p>
                        @if ($treatment->notes)
                            <p class="dk-treatment-notes">{{ $treatment->notes }}</p>
                        @endif
                        @if ($isCurrent && $treatment->due_date)
                            <p class="dk-treatment-due">Renovar: {{ $fmtDate($treatment->due_date) }}</p>
                        @endif
                    </div>
                    <div class="dk-treatment-side">
                        @if ($isCurrent)
                            <span class="dk-badge dk-badge--{{ $status }}">{{ $labels[$status] }}</span>
                        @else
                            <span class="dk-badge dk-badge--no_aplica">Renovado</span>
                        @endif

                        @if ($isCurrent && $hasOpen)
                            <span class="dk-treatment-meta">Solicitud enviada</span>
                        @elseif (isset($renewUrls[$treatment->id]))
                            <a href="{{ $renewTarget($treatment) }}" class="dk-btn-agendar"
                                aria-label="Agendar renovación de {{ $treatment->service?->name ?? 'tratamiento' }}">Agendar renovación</a>
                            @if ($hasWhatsapp)
                                <a href="{{ $renewUrls[$treatment->id] }}" class="dk-btn-link" target="_blank" rel="noopener noreferrer">o escríbenos por WhatsApp</a>
                            @endif
                        @elseif ($isCurrent && $treatment->service_id)
                            <a href="{{ route('patient.requests.create', $treatment->id) }}" class="dk-btn-link">Solicitar renovación</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="dk-empty">
                    <p><strong>Aún no tienes procedimientos registrados.</strong></p>
                    <p>Cuando te realices un tratamiento con nosotros, aquí verás la fecha y cuándo te toca renovarlo.</p>
                    <a href="{{ route('home') }}#formulario" class="dk-btn-primary">Reservar valoración</a>
                </div>
            @endforelse
        </section>

    </main>
</x-layouts.site>
