@props([
    'settings' => [],
    'title' => null,
    'description' => null,
    'image' => null,
    'bodyClass' => '',
    'noindex' => false,
])
@php
    $brand = \App\Support\SiteBrand::for($settings);
    $clinicName = $brand['clinicName'];
    $pageTitle = $title ?? ($clinicName.' | Medicina estética');
    $pageDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($description ?? \App\View\HomePage::publicText($settings['hero_subtitle'] ?? null, 'Medicina estética con criterio médico y resultados naturales.')))), 160, '…');
    $pageImage = $image ?? $brand['doctorPhotoUrl'] ?? $brand['logoUrl'];
    $onHome = request()->routeIs('home');
    $isLanding = str_contains($bodyClass, 'landing-body');

    // Datos estructurados (Google): la clínica como MedicalClinic.
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'MedicalClinic',
        'name' => $clinicName,
        'url' => route('home'),
        'image' => $pageImage,
        'logo' => $brand['logoUrl'],
        'telephone' => $brand['phone'] ?: null,
        'email' => $brand['email'] ?: null,
        'address' => $brand['address'] ? ['@type' => 'PostalAddress', 'streetAddress' => $brand['address'], 'addressCountry' => 'CO'] : null,
        'sameAs' => collect($brand['social'])->pluck('url')->values()->all() ?: null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if ($noindex)
        <meta name="robots" content="noindex, nofollow">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $clinicName }}">
    <meta property="og:locale" content="es_CO">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($pageImage)
        <meta property="og:image" content="{{ $pageImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#A8BFA3">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @unless ($noindex)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endunless
    @stack('head')
</head>
<body class="site-body dk-has-mobilebar {{ $bodyClass }}">
    <a href="#contenido" class="dk-skip-link">Saltar al contenido</a>

    @if ($isLanding)
        <div class="ambient ambient-left"></div>
        <div class="ambient ambient-right"></div>
    @endif

    <div class="site-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <a href="{{ route('home') }}" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                    @if ($brand['logoUrl'])
                        <span class="brand-logo-shell">
                            <img src="{{ $brand['logoUrl'] }}" alt="Logo de {{ $clinicName }}" class="brand-logo-mark" width="48" height="48">
                        </span>
                    @else
                        <span class="brand-monogram">{{ $brand['initials'] }}</span>
                    @endif
                    <span class="brand-copy">
                        <span class="brand-clinic-name">{{ $clinicName }}</span>
                        <span class="brand-doctor-name">{{ $brand['tagline'] }}</span>
                    </span>
                </a>

                <nav class="nav-links" aria-label="Navegación principal">
                    @include('partials.site-nav-links', ['onHome' => $onHome, 'withAccount' => false])
                </nav>

                <div class="topbar-actions">
                    {{-- "Ingresar" (secundario, borde) + "Agenda ahora" (CTA principal) a la derecha de la barra. --}}
                    @include('partials.dk-patient-nav-link')
                    <a href="{{ $brand['contactUrl'] }}" class="topbar-cta" @if(request()->routeIs('contact')) aria-current="page" @endif>{{ $settings['topbar_cta_label'] ?? 'Agenda ahora' }}</a>
                    <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Abrir menú de navegación">
                        <span class="nav-toggle-icon"><span></span><span></span><span></span></span>
                        <span class="nav-toggle-label">Menú</span>
                    </button>
                </div>
            </div>
        </header>

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
                    <a href="{{ route('home') }}" class="{{ $onHome ? 'is-active' : '' }}" @if($onHome) data-nav-section="inicio" @endif>Inicio</a>
                    @include('partials.site-nav-links', ['onHome' => $onHome])
                    <a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'is-active' : '' }}">Preguntas frecuentes</a>
                </nav>
                <a href="{{ $brand['contactUrl'] }}" class="btn btn-primary mobile-nav-cta">{{ $settings['topbar_cta_label'] ?? 'Agenda ahora' }}</a>
            </div>
        </div>

        @if (session('success'))
            @if ($isLanding)
                <div class="container landing-flash-wrap"><div class="flash-success landing-flash" role="status">{{ session('success') }}</div></div>
            @else
                <div class="flash-success" role="status">{{ session('success') }}</div>
            @endif
        @endif

        @if ($isLanding && $brand['social'])
            <aside class="social-dock" aria-label="Redes sociales de {{ $clinicName }}">
                @foreach ($brand['social'] as $item)
                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['label'] }}"><x-site-icon :name="$item['icon']" /></a>
                @endforeach
            </aside>
        @endif

        <div id="contenido">
            {{ $slot }}
        </div>

        <footer class="footer">
            <div class="container footer-inner">
                <div class="footer-brand-lockup">
                    @if ($brand['logoUrl'])
                        <span class="footer-logo-shell">
                            <img src="{{ $brand['logoUrl'] }}" alt="Logo de {{ $clinicName }}" class="footer-logo-mark" loading="lazy">
                        </span>
                    @endif
                    <div>
                        <p>{{ $clinicName }}</p>
                        <small>{{ $brand['address'] ?: $brand['tagline'] }}</small>
                    </div>
                </div>

                @if ($brand['social'])
                    <div class="footer-social" aria-label="Redes sociales">
                        @foreach ($brand['social'] as $item)
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['label'] }}" class="footer-social-link"><x-site-icon :name="$item['icon']" /></a>
                        @endforeach
                    </div>
                @endif

                <nav class="dk-footer-meta" aria-label="Enlaces del pie de página">
                    @if ($brand['phone'])<p><a href="tel:{{ preg_replace('/[^\d+]/', '', $brand['phone']) }}">{{ $brand['phone'] }}</a></p>@endif
                    @if ($brand['email'])<p><a href="mailto:{{ $brand['email'] }}">{{ $brand['email'] }}</a></p>@endif
                    <a href="{{ route('services.index') }}" class="dk-footer-legal">Tratamientos</a>
                    <a href="{{ route('team') }}" class="dk-footer-legal">Equipo médico</a>
                    <a href="{{ route('faq') }}" class="dk-footer-legal">Preguntas frecuentes</a>
                    <a href="{{ route('legal.data-policy') }}" class="dk-footer-legal">Política de datos</a>
                </nav>
            </div>
        </footer>
    </div>

    @include('partials.dk-contact-actions', ['clinicName' => $clinicName, 'bookingUrl' => $brand['bookingUrl'], 'hasWhatsapp' => $brand['hasWhatsapp'], 'phone' => $brand['phone']])
    @stack('scripts')
</body>
</html>
