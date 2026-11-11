@php
    $fmtDate = fn ($date) => $date->copy()->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $firstName = \Illuminate\Support\Str::of($patient->name)->trim()->explode(' ')->first();
    $labels = \App\Models\PatientTreatment::RENEWAL_LABELS;
    $linkAttrs = $hasWhatsapp ? 'target="_blank" rel="noopener noreferrer"' : '';
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
                @if (isset($renewUrls[$nextRenewal->id]))
                    <a href="{{ $renewUrls[$nextRenewal->id] }}" class="dk-btn-primary" {!! $linkAttrs !!}>Agendar renovación</a>
                @endif
            </section>
        @endif

        <section class="dk-history" aria-labelledby="dk-history-title">
            <h2 id="dk-history-title" class="dk-section-title">Mis procedimientos</h2>

            @forelse ($treatments as $treatment)
                @php($status = $treatment->renewal_status)
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
                        @if ($treatment->due_date)
                            <p class="dk-treatment-due">Renovar: {{ $fmtDate($treatment->due_date) }}</p>
                        @endif
                    </div>
                    <div class="dk-treatment-side">
                        <span class="dk-badge dk-badge--{{ $status }}">{{ $labels[$status] }}</span>
                        @if (isset($renewUrls[$treatment->id]))
                            <a href="{{ $renewUrls[$treatment->id] }}" class="dk-btn-agendar" {!! $linkAttrs !!}
                                aria-label="Agendar renovación de {{ $treatment->service?->name ?? 'tratamiento' }}">Agendar renovación</a>
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
