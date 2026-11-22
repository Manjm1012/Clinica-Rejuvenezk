@php
    $brand = \App\Support\SiteBrand::for($settings);
    $clinicName = $brand['clinicName'];
    $doctorPhotoUrl = \App\Support\Media::url($doctor?->photo_path);

    $aboutKicker = $settings['about_kicker'] ?? 'Quiénes somos';
    $aboutTitle = $settings['about_title'] ?? 'Medicina estética con criterio humano y respaldo médico';
    $aboutLead = $settings['about_lead'] ?? 'Somos un centro médico estético especializado en el cuidado integral de la salud y la belleza. Nuestro equipo está conformado por profesionales certificados, comprometidos con resultados naturales y progresivos.';
    $aboutDoctorLine = $settings['about_doctor_line'] ?? ('En '.$clinicName.' combinamos ciencia avanzada y diagnóstico personalizado para ofrecer tratamientos seguros, eficaces y mínimamente invasivos.');
    $aboutMission = $settings['about_mission'] ?? 'Brindar soluciones estéticas integrales que realcen la belleza natural de nuestros pacientes, combinando vanguardia médica con un trato humano y personalizado para mejorar su autoestima y calidad de vida.';
    $aboutVision = $settings['about_vision'] ?? 'Ser una clínica estética líder en la región, reconocida por la calidez en el servicio y la excelencia en resultados naturales, logrando que cada paciente se sienta la mejor versión de sí mismo.';
    $aboutImageUrl = \App\Support\Media::resolve($settings['about_image_url'] ?? null) ?? $doctorPhotoUrl ?? $brand['logoUrl'];
    $aboutImageAlt = $settings['about_image_alt'] ?? ('Especialista de '.$clinicName);

    $credentialKicker = $settings['credentials_kicker'] ?? 'Respaldo médico';
    $credentialTitle = $settings['credentials_title'] ?? 'Certificaciones y trayectoria para decidir con confianza';
    $credentialBadges = collect([
        $settings['credential_badge_1'] ?? 'Miembro de sociedad científica',
        $settings['credential_badge_2'] ?? 'Protocolos estandarizados y trazables',
        $settings['credential_badge_3'] ?? 'Tecnología con enfoque médico-estético',
    ])->filter();
@endphp

<x-layouts.site
    :settings="$settings"
    :title="$clinicName . ' | Quiénes somos'"
    :description="'Conoce la historia, el equipo y el enfoque médico estético de ' . $clinicName . '.'"
    :image="$aboutImageUrl"
    body-class="landing-body about-page-body"
>
    <main class="about-main" id="quienes-somos">
        <section class="section container about-story-hero">
            <div class="about-story-grid reveal reveal-2">
                <aside class="about-story-visual" aria-label="Imagen institucional de {{ $clinicName }}">
                    @if ($aboutImageUrl)
                        <figure class="about-media-frame about-story-frame">
                            <img src="{{ $aboutImageUrl }}" alt="{{ $aboutImageAlt }}" loading="lazy" decoding="async" class="about-media-image">
                        </figure>
                    @else
                        <div class="about-media-fallback about-story-fallback">
                            <span>{{ $brand['initials'] }}</span>
                            <small>{{ $clinicName }}</small>
                        </div>
                    @endif
                </aside>

                <article class="about-story-copy about-profile-copy">
                    <p class="kicker">{{ $aboutKicker }}</p>
                    <h1>{{ $aboutTitle }}</h1>
                    <p class="lead">{{ $aboutLead }}</p>
                    <p class="hero-editorial-note">{{ $aboutDoctorLine }}</p>
                    <div class="about-profile-meta">
                        <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                        <span>{{ $doctor?->specialty ?: 'Medicina estética facial y corporal' }}</span>
                    </div>
                    <div class="about-focus-actions" aria-label="Accesos de misión y visión">
                        <button type="button" class="about-focus-btn is-active" data-target="mision" aria-controls="mision" aria-expanded="true">Misión</button>
                        <button type="button" class="about-focus-btn" data-target="vision" aria-controls="vision" aria-expanded="false">Visión</button>
                    </div>
                </article>
            </div>
        </section>

        <section class="section section-white about-section">
            <div class="container about-layout reveal reveal-2">
                <article class="about-content-card">
                    <p class="kicker">Nuestra esencia</p>
                    <h2 class="section-title">Ciencia, criterio y acompañamiento humano.</h2>
                    <p class="about-pillars-caption">Selecciona Misión o Visión para ver el enfoque institucional del centro.</p>

                    <div class="about-pillars" aria-label="Misión y visión de {{ $clinicName }}">
                        <article class="about-pillar is-open" id="mision">
                            <h3>Misión</h3>
                            <p>{{ $aboutMission }}</p>
                        </article>
                        <article class="about-pillar" id="vision">
                            <h3>Visión</h3>
                            <p>{{ $aboutVision }}</p>
                        </article>
                    </div>
                </article>

                <aside class="about-credibility-card">
                    <p class="kicker">{{ $credentialKicker }}</p>
                    <h2 class="section-title">{{ $credentialTitle }}</h2>
                    <div class="about-credential-list">
                        @foreach ($credentialBadges as $badge)
                            <span>{{ $badge }}</span>
                        @endforeach
                    </div>

                    <div class="about-specialist-card">
                        <div>
                            <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                            <span>{{ $doctor?->specialty ?: 'Medicina estética facial y corporal' }}</span>
                        </div>
                        <a href="{{ route('team') }}" class="svc-card-link">Conoce al equipo médico →</a>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</x-layouts.site>
