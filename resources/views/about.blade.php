@php
    $clinicName = $settings['clinic_name'] ?? 'Clínica Rejuvenezk';
    $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
    $normalizeMediaPath = function (?string $path): ?string {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = trim($path);

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            $normalized = parse_url($normalized, PHP_URL_PATH) ?: $normalized;
        }

        $normalized = ltrim(rawurldecode($normalized), '/');

        foreach (['media/', 'storage/', 'public/', 'app/public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return $normalized !== '' ? $normalized : null;
    };

    $clinicLogoPath = $normalizeMediaPath($clinic?->logo_path);
    $doctorPhotoPath = $normalizeMediaPath($doctor?->photo_path);
    $clinicLogoUrl = $clinicLogoPath && $publicDisk->exists($clinicLogoPath)
        ? $publicDisk->url($clinicLogoPath)
        : null;
    $doctorPhotoUrl = $doctorPhotoPath && $publicDisk->exists($doctorPhotoPath)
        ? $publicDisk->url($doctorPhotoPath)
        : null;
    $brandInitials = collect(preg_split('/\s+/', trim($clinicName)))
        ->filter()
        ->take(2)
        ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');

    $aboutKicker = $settings['about_kicker'] ?? 'Quiénes somos';
    $aboutTitle = $settings['about_title'] ?? 'Medicina estética con criterio humano y respaldo médico';
    $aboutLead = $settings['about_lead'] ?? 'Somos un centro médico estético especializado en el cuidado integral de la salud y la belleza. Nuestro equipo está conformado por profesionales certificados, comprometidos con resultados naturales y progresivos.';
    $aboutDoctorLine = $settings['about_doctor_line'] ?? ('En ' . $clinicName . ' combinamos ciencia avanzada y diagnóstico personalizado para ofrecer tratamientos seguros, eficaces y mínimamente invasivos.');
    $aboutMission = $settings['about_mission'] ?? 'Brindar soluciones estéticas integrales que realcen la belleza natural de nuestros pacientes, combinando vanguardia médica con un trato humano y personalizado para mejorar su autoestima y calidad de vida.';
    $aboutVision = $settings['about_vision'] ?? 'Ser una clínica estética líder en la región, reconocida por la calidez en el servicio y la excelencia en resultados naturales, logrando que cada paciente se sienta la mejor versión de sí mismo.';
    $topbarCtaLabel = $settings['topbar_cta_label'] ?? 'Agenda ahora';

    $aboutImageUrl = null;
    $aboutImagePathCandidate = trim((string) ($settings['about_image_url'] ?? ''));
    if ($aboutImagePathCandidate !== '') {
        if (filter_var($aboutImagePathCandidate, FILTER_VALIDATE_URL)) {
            $aboutImageUrl = $aboutImagePathCandidate;
        } else {
            $normalizedAboutImagePath = $normalizeMediaPath($aboutImagePathCandidate);
            if ($normalizedAboutImagePath && $publicDisk->exists($normalizedAboutImagePath)) {
                $aboutImageUrl = $publicDisk->url($normalizedAboutImagePath);
            }
        }
    }

    if (! $aboutImageUrl) {
        $aboutImageUrl = $doctorPhotoUrl ?: $clinicLogoUrl;
    }

    $aboutImageAlt = $settings['about_image_alt'] ?? ('Especialista de ' . $clinicName);
    $whatsappRawUrl = trim((string) ($settings['whatsapp_url'] ?? ''));
    $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
    $hasWhatsappLink = $whatsappRawUrl !== '' || $whatsappDigits !== '';
    $whatsappUrl = $whatsappRawUrl !== ''
        ? $whatsappRawUrl
        : ($whatsappDigits ? 'https://wa.me/' . $whatsappDigits : route('home') . '#contacto');
    $credentialKicker = $settings['credentials_kicker'] ?? 'Respaldo médico';
    $credentialTitle = $settings['credentials_title'] ?? 'Certificaciones y trayectoria para decidir con confianza';
    $credentialBadges = collect([
        $settings['credential_badge_1'] ?? 'Miembro de sociedad científica',
        $settings['credential_badge_2'] ?? 'Protocolos estandarizados y trazables',
        $settings['credential_badge_3'] ?? 'Tecnología con enfoque médico-estético',
    ])->filter();
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $clinicName }} | Quiénes somos</title>
    <meta name="description" content="Conoce la historia, el equipo y el enfoque médico estético de {{ $clinicName }}.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body landing-body about-page-body">
    <div class="ambient ambient-left"></div>
    <div class="ambient ambient-right"></div>

    <header class="topbar reveal reveal-1">
        <div class="container topbar-inner">
            <a href="{{ route('home') }}" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                @if ($clinicLogoUrl)
                    <span class="brand-logo-shell">
                        <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="brand-logo-mark">
                    </span>
                @else
                    <span class="brand-monogram">{{ $brandInitials ?: 'CR' }}</span>
                @endif
                <span class="brand-copy">
                    <strong>{{ $clinicName }}</strong>
                    <small>{{ $doctor?->name ?: 'Medicina estética facial y corporal' }}</small>
                </span>
            </a>
            <nav class="nav-links" aria-label="Navegación principal">
                <a href="{{ route('home') }}">Inicio</a>
                <a href="{{ route('about') }}" class="is-active">Quiénes somos</a>
                <a href="{{ route('services.index') }}">Servicios</a>
                <a href="{{ route('home') }}#contacto">Contacto</a>
            </nav>
            <div class="topbar-actions">
                <a href="{{ $whatsappUrl }}" class="btn btn-outline topbar-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
                <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Abrir menú de navegación">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        <div class="mobile-nav-overlay" hidden></div>
        <div class="mobile-nav-panel" id="mobile-nav-panel" hidden>
            <div class="mobile-nav-shell">
                <div class="mobile-nav-head">
                    <div class="mobile-nav-brand">
                        <span>{{ $clinicName }}</span>
                        <small>Navegación</small>
                    </div>
                    <button type="button" class="mobile-nav-close" aria-label="Cerrar menú de navegación">×</button>
                </div>
                <nav class="mobile-nav-links" aria-label="Navegación móvil">
                    <a href="{{ route('home') }}">Inicio</a>
                    <a href="{{ route('about') }}">Quiénes somos</a>
                    <a href="{{ route('services.index') }}">Servicios</a>
                    <a href="{{ route('home') }}#contacto">Contacto</a>
                </nav>
                <a href="{{ $whatsappUrl }}" class="btn btn-primary mobile-nav-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
            </div>
        </div>
    </header>

    <main class="about-main" id="quienes-somos">
        <section class="section container about-story-hero">
            <div class="about-story-grid reveal reveal-2">
                <article class="about-story-copy">
                    <p class="kicker">{{ $aboutKicker }}</p>
                    <h1>{{ $aboutTitle }}</h1>
                    <p class="lead">{{ $aboutLead }}</p>
                    <p class="hero-editorial-note">{{ $aboutDoctorLine }}</p>
                    <div class="hero-actions about-story-actions">
                        <a href="{{ route('home') }}#contacto" class="btn btn-primary">Agendar valoración</a>
                        <a href="{{ route('services.index') }}" class="btn btn-ghost">Ver tratamientos</a>
                    </div>
                </article>

                <aside class="about-story-visual" aria-label="Imagen institucional de {{ $clinicName }}">
                    @if ($aboutImageUrl)
                        <figure class="about-media-frame about-story-frame">
                            <img src="{{ $aboutImageUrl }}" alt="{{ $aboutImageAlt }}" loading="lazy" class="about-media-image">
                        </figure>
                    @else
                        <div class="about-media-fallback about-story-fallback">
                            <span>{{ $brandInitials ?: 'CR' }}</span>
                            <small>{{ $clinicName }}</small>
                        </div>
                    @endif
                </aside>
            </div>
        </section>

        <section class="section section-white about-section">
            <div class="container about-layout reveal reveal-2">
                <article class="about-content-card">
                    <p class="kicker">Nuestra esencia</p>
                    <h2 class="section-title">Ciencia, criterio y acompañamiento humano.</h2>
                    <p class="section-intro about-intro">{{ $aboutLead }}</p>
                    <p class="about-doctor-line">{{ $aboutDoctorLine }}</p>

                    <div class="about-pillars" aria-label="Misión y visión de {{ $clinicName }}">
                        <article class="about-pillar">
                            <h3>Misión</h3>
                            <p>{{ $aboutMission }}</p>
                        </article>
                        <article class="about-pillar">
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
                        @if ($doctorPhotoUrl)
                            <img src="{{ $doctorPhotoUrl }}" alt="Foto del doctor {{ $doctor?->name ?: $clinicName }}" loading="lazy">
                        @elseif ($clinicLogoUrl)
                            <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" loading="lazy">
                        @endif
                        <div>
                            <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                            <span>{{ $doctor?->specialty ?: 'Medicina estética facial y corporal' }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <div class="footer-brand-lockup">
                @if ($clinicLogoUrl)
                    <span class="footer-logo-shell">
                        <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="footer-logo-mark">
                    </span>
                @endif
                <div>
                    <p>{{ $clinicName }}</p>
                    <small>{{ $doctor?->name ?: 'Direccion medica estetica' }}</small>
                </div>
            </div>
            <p>{{ $settings['phone'] ?: 'Atencion comercial premium' }}</p>
        </div>
    </footer>
</body>
</html>