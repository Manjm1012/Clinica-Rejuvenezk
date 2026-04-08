@php
    $clinicName = $settings['clinic_name'] ?? 'Clinica Rejuvenezk';
    $heroKicker = $settings['hero_kicker'] ?? 'Medicina estetica avanzada';
    $heroTitle = $settings['hero_title'] ?? 'Rejuvenecimiento facial con resultados naturales.';
    $heroSubtitle = $settings['hero_subtitle'] ?? 'Tecnologia avanzada, criterio medico y protocolos personalizados para armonizar tu rostro sin perder expresion.';
    $heroPrimaryCta = $settings['hero_primary_cta'] ?? 'Reservar valoracion';
    $heroSecondaryCta = $settings['hero_secondary_cta'] ?? 'Ver tratamientos';
    $heroCardKicker = $settings['hero_card_kicker'] ?? 'Primera cita';
    $heroCardTitle = $settings['hero_card_title'] ?? ($doctor?->subtitle ?: 'Diagnostico inteligente de piel');
    $heroCardText = $settings['hero_card_text'] ?? ($doctor?->bio ?: 'Analisis de calidad cutanea y plan estetico por etapas para resultados armonicos y progresivos.');
    $heroStackPrimaryLabel = $settings['hero_stack_primary_label'] ?? 'Protocolo estrella';
    $heroStackSecondaryLabel = $settings['hero_stack_secondary_label'] ?? 'Especialista';
    $servicesKicker = $settings['services_kicker'] ?? 'Nuestros tratamientos';
    $servicesTitle = $settings['services_title'] ?? 'Tratamientos seleccionados con enfoque medico estetico';
    $servicesLead = $settings['services_lead'] ?? 'Cada protocolo combina diagnostico, tecnica y naturalidad para construir resultados elegantes, progresivos y coherentes con tu rostro.';
    $benefitsKicker = $settings['benefits_kicker'] ?? 'Nuestra promesa';
    $benefitsTitle = $settings['benefits_title'] ?? 'Una experiencia estetica pensada para verse bien y sentirse correcta.';
    $benefitsLead = $settings['benefits_lead'] ?? 'Combinamos precision medica, criterio facial y acompanamiento cercano para que cada decision se tome con confianza.';
    $testimonialsKicker = $settings['testimonials_kicker'] ?? 'Testimonios';
    $testimonialsTitle = $settings['testimonials_title'] ?? 'Pacientes que ya viven su cambio';
    $socialKicker = $settings['social_kicker'] ?? ('Comunidad ' . $clinicName);
    $socialTitle = $settings['social_title'] ?? 'Conecta con nuestras redes y canales';
    $socialLead = $settings['social_lead'] ?? 'Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.';
    $ctaKicker = $settings['cta_kicker'] ?? 'Agenda tu cita';
    $ctaTitle = $settings['cta_title'] ?? 'Empieza tu valoracion con una experiencia clara y personalizada.';
    $ctaBody = $settings['cta_body'] ?? 'Atencion medica estetica, seguimiento ordenado y una propuesta pensada para tus objetivos reales.';
    $ctaNote = $settings['cta_note'] ?? 'Respuesta comercial por WhatsApp o correo con orientacion inicial y siguientes pasos.';
    $topbarCtaLabel = $settings['topbar_cta_label'] ?? 'Agenda ahora';
    $ctaWhatsappLabel = $settings['cta_whatsapp_label'] ?? 'WhatsApp';
    $ctaEmailLabel = $settings['cta_email_label'] ?? 'Solicitar informacion';
    $whatsappRawUrl = trim((string) ($settings['whatsapp_url'] ?? ''));
    $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
    $hasWhatsappLink = $whatsappRawUrl !== '' || $whatsappDigits !== '';
    $whatsappUrl = $whatsappRawUrl !== ''
        ? $whatsappRawUrl
        : ($whatsappDigits ? 'https://wa.me/' . $whatsappDigits : '#contacto');
    $clinicLogoUrl = !empty($clinic?->logo_path) ? asset('storage/' . $clinic->logo_path) : null;
    $doctorPhotoUrl = !empty($doctor?->photo_path) ? asset('storage/' . $doctor->photo_path) : null;
    $brandInitials = collect(preg_split('/\s+/', trim($clinicName)))
        ->filter()
        ->take(2)
        ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');
    $bannerMedia = null;

    if (!empty($featuredServices->first()?->banner_path)) {
        $bannerMedia = asset('storage/' . $featuredServices->first()->banner_path);
    } elseif (!empty($featuredServices->first()?->image_path)) {
        $bannerMedia = asset('storage/' . $featuredServices->first()->image_path);
    }

    $heroMetrics = $stats->take(2)->map(fn ($stat) => [
        'value' => $stat->value,
        'label' => $stat->label,
    ]);

    if ($heroMetrics->isEmpty()) {
        $heroMetrics = collect([
            ['value' => '+4,800', 'label' => 'Pacientes atendidos'],
            ['value' => '12 anos', 'label' => 'Experiencia clinica'],
        ]);
    }

    $heroEditorialNote = $doctor?->name
        ? 'Valoracion dirigida por ' . $doctor->name . ' con enfoque medico estetico y protocolos a medida.'
        : 'Valoraciones personalizadas con criterio medico, tecnologia avanzada y resultados armonicos.';

    $heroSubtitle = trim((string) $heroSubtitle);
    if (mb_strlen($heroSubtitle) > 210) {
        $heroSubtitle = \Illuminate\Support\Str::limit($heroSubtitle, 210, '...');
    }

    $heroEditorialNote = trim((string) $heroEditorialNote);
    if (mb_strlen($heroEditorialNote) > 150) {
        $heroEditorialNote = \Illuminate\Support\Str::limit($heroEditorialNote, 150, '...');
    }

    $instagramHandle = '@drkevin_rejuvenezk';

    if (!empty($settings['instagram_url'])) {
        $instagramPath = trim((string) parse_url($settings['instagram_url'], PHP_URL_PATH), '/');

        if ($instagramPath !== '') {
            $instagramHandle = '@' . $instagramPath;
        }
    }

    $socialCards = collect([
        ['label' => 'Instagram', 'icon' => 'instagram', 'handle' => $instagramHandle, 'text' => 'Antes y despues, reels y tips de cuidado', 'url' => $settings['instagram_url'] ?? null],
        ['label' => 'Facebook', 'icon' => 'facebook', 'handle' => 'Perfil oficial', 'text' => 'Novedades, eventos y promociones especiales', 'url' => $settings['facebook_url'] ?? null],
        ['label' => 'TikTok', 'icon' => 'tiktok', 'handle' => '@rejuvenezk', 'text' => 'Videos de procedimientos y contenido educativo', 'url' => $settings['tiktok_url'] ?? null],
        ['label' => 'YouTube', 'icon' => 'youtube', 'handle' => 'Rejuvenezk TV', 'text' => 'Testimonios completos y explicacion medica', 'url' => $settings['youtube_url'] ?? null],
        ['label' => 'WhatsApp', 'icon' => 'whatsapp', 'handle' => $settings['phone'] ?: 'Agenda inmediata', 'text' => 'Atencion comercial y seguimiento de valoraciones', 'url' => $hasWhatsappLink ? $whatsappUrl : null],
        ['label' => 'Correo', 'icon' => 'mail', 'handle' => $settings['email'] ?: 'contacto@rejuvenezk.com', 'text' => 'Solicita informacion detallada y propuestas personalizadas', 'url' => !empty($settings['email']) ? 'mailto:' . $settings['email'] : null],
    ]);

    $socialIcons = [
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.9A3.85 3.85 0 0 0 3.9 7.75v8.5a3.85 3.85 0 0 0 3.85 3.85h8.5a3.85 3.85 0 0 0 3.85-3.85v-8.5a3.85 3.85 0 0 0-3.85-3.85h-8.5Zm8.9 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9Z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.5 1.7-1.5h1.6V5c-.8-.1-1.7-.2-2.6-.2-2.6 0-4.2 1.6-4.2 4.4V11H8v3h2.5v8h3Z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.9 3c.2 1.8 1.2 3.4 2.8 4.3 1 .6 2.1.9 3.3.9v3.1c-1.4 0-2.7-.3-3.9-.9-.8-.4-1.5-.9-2.2-1.5v6.1c0 3.4-2.7 6-6.1 6a6 6 0 0 1-6-6c0-3.4 2.7-6.1 6-6.1.3 0 .6 0 .9.1v3.1a3 3 0 1 0 2.2 2.9V3h3Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12s0-3-.4-4.4a3.1 3.1 0 0 0-2.2-2.2C18 5 12 5 12 5s-6 0-7.4.4a3.1 3.1 0 0 0-2.2 2.2C2 9 2 12 2 12s0 3 .4 4.4a3.1 3.1 0 0 0 2.2 2.2C6 19 12 19 12 19s6 0 7.4-.4a3.1 3.1 0 0 0 2.2-2.2C22 15 22 12 22 12Zm-12.3 3.5V8.5l5.8 3.5-5.8 3.5Z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.2a9.8 9.8 0 0 0-8.4 14.9L2 22l5-1.6A9.8 9.8 0 1 0 12 2.2Zm0 17.8c-1.5 0-3-.4-4.2-1.2l-.3-.2-2.9.9.9-2.8-.2-.3A7.8 7.8 0 1 1 12 20Zm4.3-5.9c-.2-.1-1.3-.6-1.5-.7s-.4-.1-.6.1-.7.7-.8.8-.3.2-.5.1a6.4 6.4 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.4.1-.5l.4-.4c.1-.1.2-.2.2-.4l.1-.3c0-.1-.6-1.6-.8-2.1-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.3c.1.2 1.6 2.5 3.9 3.5.5.2 1 .4 1.4.5.6.2 1.1.2 1.5.1.5-.1 1.3-.5 1.5-1 .2-.5.2-.9.1-1 0-.1-.2-.2-.4-.3Z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5h18A1.5 1.5 0 0 1 22.5 7v10A1.5 1.5 0 0 1 21 18.5H3A1.5 1.5 0 0 1 1.5 17V7A1.5 1.5 0 0 1 3 5.5Zm0 1.7a.3.3 0 0 0-.3.3v.2l9.3 6.3 9.3-6.3v-.2a.3.3 0 0 0-.3-.3H3Zm18 9.6a.3.3 0 0 0 .3-.3V9.8l-8.8 6a.9.9 0 0 1-1 0l-8.8-6v6.7c0 .2.1.3.3.3H21Z"/></svg>',
    ];

    $testimonialsToShow = $testimonials->take(3);
    $reviewsUrl = trim((string) ($settings['reviews_url'] ?? ''));
    $testimonialsCount = $testimonials->count();
    $testimonialsAvg = $testimonialsCount > 0 ? number_format((float) $testimonials->avg('rating'), 1) : '4.9';
    $credentialKicker = $settings['credentials_kicker'] ?? 'Respaldo medico';
    $credentialTitle = $settings['credentials_title'] ?? 'Certificaciones y trayectoria para decidir con confianza';

    $credentialBadges = collect([
        $settings['credential_badge_1'] ?? 'Miembro de sociedad cientifica',
        $settings['credential_badge_2'] ?? 'Protocolos estandarizados y trazables',
        $settings['credential_badge_3'] ?? 'Tecnologia con enfoque medico-estetico',
    ])->filter();

    $benefitCards = collect([
        [
            'title' => 'Resultados armoniosos',
            'text' => 'Realzamos tus facciones con una mirada natural, sin exageraciones ni cambios ajenos a tu rostro.',
            'label' => 'Naturalidad',
        ],
        [
            'title' => 'Seguridad clinica',
            'text' => 'Protocolos medicos, tecnologia confiable y decisiones guiadas por evaluacion profesional.',
            'label' => 'Confianza',
        ],
        [
            'title' => 'Criterio personalizado',
            'text' => 'Cada tratamiento se diseña segun tu anatomia, tus objetivos y el ritmo adecuado para ti.',
            'label' => 'Precision',
        ],
    ]);
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $clinicName }} | Medicina Estetica Premium</title>
    <meta name="description" content="{{ $heroSubtitle }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body landing-body">
    <div class="ambient ambient-left"></div>
    <div class="ambient ambient-right"></div>

    <header class="topbar reveal reveal-1">
        <div class="container topbar-inner">
            <a href="#inicio" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                @if ($clinicLogoUrl)
                    <span class="brand-logo-shell">
                        <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="brand-logo-mark">
                    </span>
                @else
                    <span class="brand-monogram">{{ $brandInitials ?: 'CR' }}</span>
                @endif
                <span class="brand-copy">
                    <strong>{{ $clinicName }}</strong>
                    <small>{{ $doctor?->name ?: 'Medicina estetica facial y corporal' }}</small>
                </span>
            </a>
            <nav class="nav-links" aria-label="Navegacion principal">
                <a href="#servicios">Servicios</a>
                <a href="#resultados">Resultados</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <a href="{{ $whatsappUrl }}" class="btn btn-outline" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
        </div>
    </header>

    @if (session('success'))
        <div class="container landing-flash-wrap">
            <div class="flash-success landing-flash">{{ session('success') }}</div>
        </div>
    @endif

    <aside class="social-dock" aria-label="Redes sociales de {{ $clinicName }}">
        @foreach ($socialCards->take(4) as $card)
            <a href="{{ $card['url'] ?: '#redes' }}" @if($card['url']) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $card['label'] }}">
                {!! $socialIcons[$card['icon']] ?? '' !!}
            </a>
        @endforeach
    </aside>

    <a href="{{ $whatsappUrl }}" class="whatsapp-fab" aria-label="Abrir WhatsApp de {{ $clinicName }}" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>
        <span aria-hidden="true">WA</span>
        <strong>WhatsApp</strong>
    </a>

    <main id="inicio">
        <section class="hero section container">
            <div class="hero-copy reveal reveal-2">
                <p class="kicker">{{ $heroKicker }}</p>
                <h1>{{ $heroTitle }}</h1>
                <p class="lead">{{ $heroSubtitle }}</p>
                <p class="hero-editorial-note">{{ $heroEditorialNote }}</p>
                <div class="hero-actions">
                    <a href="#contacto" class="btn btn-primary">{{ $heroPrimaryCta }}</a>
                    <a href="#servicios" class="btn btn-ghost">{{ $heroSecondaryCta }}</a>
                </div>
                <div class="hero-metrics">
                    @foreach ($heroMetrics as $metric)
                        <article>
                            <strong>{{ $metric['value'] }}</strong>
                            <span>{{ $metric['label'] }}</span>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="hero-panel reveal reveal-3" aria-label="Resumen de experiencia {{ $clinicName }}">
                <div class="hero-card landing-hero-card hero-portrait-card">
                    <div class="hero-portrait-media">
                        @if ($doctorPhotoUrl)
                            <img src="{{ $doctorPhotoUrl }}" alt="Foto del doctor {{ $doctor?->name ?: $clinicName }}" loading="lazy" class="hero-portrait-image">
                            <div class="hero-portrait-note">
                                <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                                <span>{{ $doctor?->specialty ?: 'Medicina estetica facial y corporal' }}</span>
                            </div>
                        @elseif ($clinicLogoUrl)
                            <div class="hero-portrait-fallback hero-logo-fallback">
                                <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}">
                            </div>
                        @else
                            <div class="hero-portrait-fallback">
                                <span>{{ $brandInitials ?: 'CR' }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="hero-portrait-copy">
                        <div class="hero-brand-chip">
                            <div>
                                <span>{{ $heroCardKicker }}</span>
                                <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                            </div>
                        </div>
                        <h2>{{ $doctor?->subtitle ?: $heroCardTitle }}</h2>
                        <p>{{ $heroCardText }}</p>
                    </div>
                </div>
                <div class="hero-stack hero-stack-editorial">
                    <div class="mini-card hero-accent-card">
                        <span>{{ $heroStackPrimaryLabel }}</span>
                        <strong>{{ $featuredServices->first()?->name ?: 'Lift & Glow 360' }}</strong>
                    </div>
                    <div class="mini-card hero-accent-card-muted">
                        <span>{{ $heroStackSecondaryLabel }}</span>
                        <strong>{{ $doctor?->name ?: 'Equipo medico premium' }}</strong>
                    </div>
                </div>
            </aside>
        </section>

        <section class="container banner-slot reveal reveal-2" aria-label="Espacio principal de impacto visual">
            @if ($bannerMedia)
                <figure class="media-placeholder media-placeholder-lg media-banner">
                    <img src="{{ $bannerMedia }}" alt="Imagen principal de {{ $clinicName }}" loading="lazy">
                </figure>
            @else
                <figure class="media-placeholder media-placeholder-lg media-banner media-banner-fallback">
                    <figcaption>
                        @if ($clinicLogoUrl)
                            <span class="banner-logo-wrap">
                                <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="banner-logo">
                            </span>
                        @endif
                        <span class="kicker">Experiencia premium</span>
                        <strong>{{ $clinicName }}</strong>
                    </figcaption>
                </figure>
            @endif
        </section>

        <section class="section benefits-section">
            <div class="container reveal reveal-2">
                <div class="benefits-head">
                    <div>
                        <p class="kicker">{{ $benefitsKicker }}</p>
                        <h2 class="section-title">{{ $benefitsTitle }}</h2>
                    </div>
                    <p class="section-intro benefits-intro">{{ $benefitsLead }}</p>
                </div>

                <div class="benefits-grid">
                    @foreach ($benefitCards as $benefit)
                        <article class="benefit-card">
                            <span class="benefit-card-label">{{ $benefit['label'] }}</span>
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section section-white trust-section">
            <div class="container trust-layout reveal reveal-2">
                <div class="trust-copy">
                    <p class="kicker">{{ $credentialKicker }}</p>
                    <h2 class="section-title trust-title">{{ $credentialTitle }}</h2>
                    <div class="credential-badges" aria-label="Credenciales de confianza">
                        @foreach ($credentialBadges as $badge)
                            <span>{{ $badge }}</span>
                        @endforeach
                    </div>
                </div>

                <article class="social-proof-card" aria-label="Calificacion de pacientes">
                    <p class="social-proof-kicker">Experiencia de pacientes</p>
                    <div class="social-proof-score">
                        <strong>{{ $testimonialsAvg }}</strong>
                        <span>/ 5</span>
                    </div>
                    <p class="social-proof-meta">Basado en {{ $testimonialsCount > 0 ? $testimonialsCount : 16 }} valoraciones visibles.</p>
                    @if ($reviewsUrl !== '')
                        <a href="{{ $reviewsUrl }}" target="_blank" rel="noopener noreferrer" class="social-proof-link">Ver resenas verificadas</a>
                    @else
                        <a href="#resultados" class="social-proof-link">Ver testimonios</a>
                    @endif
                </article>
            </div>
        </section>

        <section id="servicios" class="section section-soft section-beige">
            <div class="container reveal reveal-2">
                <div class="services-head">
                    <div>
                        <p class="kicker">{{ $servicesKicker }}</p>
                        <h2 class="section-title">{{ $servicesTitle }}</h2>
                    </div>
                    <p class="section-intro service-intro">{{ $servicesLead }}</p>
                </div>

                @if ($serviceCategories->count() > 1)
                    <div class="services-tabs" role="tablist" aria-label="Categorías de tratamientos">
                        @foreach ($serviceCategories as $i => $category)
                            <button
                                class="services-tab {{ $i === 0 ? 'is-active' : '' }}"
                                role="tab"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                aria-controls="cat-{{ $category->id }}"
                                data-tab="cat-{{ $category->id }}"
                                type="button"
                            >{{ $category->name }}</button>
                        @endforeach
                    </div>
                @endif

                @forelse ($serviceCategories as $i => $category)
                    <div
                        class="services-panel {{ $i === 0 ? 'is-active' : '' }}"
                        id="cat-{{ $category->id }}"
                        role="tabpanel"
                    >
                        @php
                            $featuredCategoryServices = $category->services->take(3);
                            $remainingCategoryServices = $category->services->skip(3)->take(6);
                        @endphp

                        @if ($category->description)
                            <p class="services-panel-intro">{{ $category->description }}</p>
                        @endif
                        <div class="services-curated-layout">
                            <aside class="services-category-card">
                                <span class="services-category-count">{{ $category->services->count() }} tratamientos</span>
                                <h3>{{ $category->name }}</h3>
                                <p>{{ $category->description ?: 'Seleccionamos los procedimientos mas representativos de esta categoria para ayudarte a decidir con mas claridad.' }}</p>

                                @if ($remainingCategoryServices->isNotEmpty())
                                    <div class="services-more-wrap">
                                        <span class="services-more-label">Tambien trabajamos</span>
                                        <div class="services-more-list">
                                            @foreach ($remainingCategoryServices as $service)
                                                <span>{{ $service->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="services-panel-cta">
                                    <a href="{{ $whatsappUrl }}" class="btn btn-primary" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>Quiero asesoria de esta categoria</a>
                                </div>
                            </aside>

                            <div class="services-grid services-grid-curated">
                                @foreach ($featuredCategoryServices as $service)
                                    <article class="landing-service-card">
                                        @if ($service->image_path)
                                            <figure class="media-placeholder media-placeholder-service service-media">
                                                <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->name }}" loading="lazy">
                                            </figure>
                                        @endif
                                        <div class="service-card-copy">
                                            <h3>{{ $service->name }}</h3>
                                            <p>{{ $service->short_description ?: 'Tratamiento personalizado con enfoque medico, seguridad y resultados naturales.' }}</p>
                                            <div class="service-links">
                                                <a href="{{ route('services.show', $service) }}">Ver detalle</a>
                                                <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="services-panel is-active">
                        <div class="services-grid">
                            @foreach ($featuredServices as $service)
                                <article class="landing-service-card">
                                    @if ($service->image_path)
                                        <figure class="media-placeholder media-placeholder-service service-media">
                                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->name }}" loading="lazy">
                                        </figure>
                                    @endif
                                    <div class="service-card-copy">
                                        <h3>{{ $service->name }}</h3>
                                        <p>{{ $service->short_description ?: 'Tratamiento personalizado con enfoque medico, seguridad y resultados naturales.' }}</p>
                                        <div class="service-links">
                                            <a href="{{ route('services.show', $service) }}">Ver detalle</a>
                                            <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div class="services-panel-cta">
                            <a href="{{ $whatsappUrl }}" class="btn btn-primary" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>Quiero asesoria por WhatsApp</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section id="resultados" class="section section-white">
            <div class="container reveal reveal-2">
                <p class="kicker">{{ $testimonialsKicker }}</p>
                <h2 class="section-title">{{ $testimonialsTitle }}</h2>
            </div>
            <div class="container testimonials-grid reveal reveal-3">
                @forelse ($testimonialsToShow as $testimonial)
                    <blockquote class="quote-card">
                        <div class="testimonial-rating">{{ str_repeat('★', max(1, (int) $testimonial->rating)) }}</div>
                        <p>"{{ $testimonial->content }}"</p>
                        <cite>{{ $testimonial->patient_name }}{{ $testimonial->reviewed_at ? ' | ' . $testimonial->reviewed_at->format('Y') : '' }}</cite>
                    </blockquote>
                @empty
                    <blockquote class="quote-card">
                        <p>"Me encanto porque respetaron mis facciones y me explicaron cada paso. Me veo fresca, no cambiada."</p>
                        <cite>Paciente Rejuvenezk</cite>
                    </blockquote>
                    <blockquote class="quote-card">
                        <p>"El trato es impecable y los resultados se notan desde la primera sesion. Volvere sin duda."</p>
                        <cite>Paciente Rejuvenezk</cite>
                    </blockquote>
                    <blockquote class="quote-card">
                        <p>"Senti confianza total por el enfoque medico. El plan fue claro y super personalizado."</p>
                        <cite>Paciente Rejuvenezk</cite>
                    </blockquote>
                @endforelse
            </div>
        </section>

        <section id="redes" class="section section-soft section-beige social-section">
            <div class="container reveal reveal-2">
                <p class="kicker">{{ $socialKicker }}</p>
                <h2 class="section-title">{{ $socialTitle }}</h2>
                <p class="lead social-lead">{{ $socialLead }}</p>

                <div class="social-grid">
                    @foreach ($socialCards as $card)
                        <a class="social-card" href="{{ $card['url'] ?: '#contacto' }}" @if($card['url']) target="_blank" rel="noopener noreferrer" @endif aria-label="Ir a {{ $card['label'] }}">
                            <span class="social-card-icon" aria-hidden="true">{!! $socialIcons[$card['icon']] ?? '' !!}</span>
                            <strong>{{ $card['label'] }}</strong>
                            <span>{{ $card['handle'] }}</span>
                            <small>{{ $card['text'] }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contacto" class="section section-white cta-wrap">
            <div class="container contact-layout reveal reveal-2">
                <div class="cta">
                    <div>
                        <p class="kicker">{{ $ctaKicker }}</p>
                        <h2>{{ $ctaTitle }}</h2>
                        <p>{{ $settings['address'] ?: $ctaBody }}</p>
                        <p class="cta-note">{{ $ctaNote }}</p>
                    </div>
                    <div class="cta-actions">
                        <a class="btn btn-primary" href="{{ $whatsappUrl }}" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $ctaWhatsappLabel }}</a>
                        <a class="btn btn-ghost" href="{{ !empty($settings['email']) ? 'mailto:' . $settings['email'] : '#formulario' }}">{{ $settings['email'] ?: $ctaEmailLabel }}</a>
                    </div>
                </div>

                <form id="formulario" method="POST" action="{{ route('leads.store') }}" class="contact-form landing-contact-form reveal reveal-3">
                    @csrf
                    <input type="hidden" name="source" value="website">
                    <input type="text" name="name" placeholder="Nombre completo" value="{{ old('name') }}" required>
                    <input type="text" name="phone" placeholder="WhatsApp / Telefono" value="{{ old('phone') }}" required>
                    <input type="email" name="email" placeholder="Correo" value="{{ old('email') }}">
                    <select name="service_id">
                        <option value="">Tratamiento de interes</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="message" rows="5" placeholder="Cuentanos que objetivo estetico quieres trabajar">{{ old('message') }}</textarea>
                    <button type="submit" class="btn btn-primary btn-submit">Enviar solicitud</button>
                </form>
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
