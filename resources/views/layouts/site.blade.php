@php
    $clinicName = $settings['clinic_name'] ?? 'Clínica Rejuvenezk';
    $doctorLine = $settings['doctor_name'] ?? 'Medicina estética facial y corporal';
    $topbarCtaLabel = $settings['topbar_cta_label'] ?? 'Agenda ahora';
    $whatsappRawUrl = trim((string) ($settings['whatsapp_url'] ?? ''));
    $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
    $hasWhatsappLink = $whatsappRawUrl !== '' || $whatsappDigits !== '';
    $whatsappUrl = $whatsappRawUrl !== ''
        ? $whatsappRawUrl
        : ($whatsappDigits ? 'https://wa.me/' . $whatsappDigits : route('home') . '#contacto');
    $brandInitials = collect(preg_split('/\s+/', trim($clinicName)))
        ->filter()
        ->take(2)
        ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $clinicName }}</title>
    <meta name="description" content="Plataforma web comercializable para clínica estética con CRM y WhatsApp integrados.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <div class="site-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <a href="{{ route('home') }}" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                    <span class="brand-monogram">{{ $brandInitials ?: 'CR' }}</span>
                    <span class="brand-copy">
                        <strong>{{ $clinicName }}</strong>
                        <small>{{ $doctorLine }}</small>
                    </span>
                </a>

                <nav class="nav-links" aria-label="Navegación principal">
                    <a href="{{ route('home') }}#inicio">Inicio</a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}">Quiénes somos</a>
                    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-active' : '' }}">Servicios</a>
                    <a href="{{ route('home') }}#resultados">Resultados</a>
                    <a href="{{ route('home') }}#contacto">Contacto</a>
                </nav>

                <div class="topbar-actions">
                    <a href="{{ $whatsappUrl }}" class="btn btn-outline topbar-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
                    <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Abrir menú de navegación">
                        <span class="nav-toggle-icon"><span></span><span></span><span></span></span>
                        <span class="nav-toggle-label">Menú</span>
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
                        <button type="button" class="mobile-nav-close" aria-label="Cerrar menú de navegación">&times;</button>
                    </div>
                    <nav class="mobile-nav-links" aria-label="Navegación móvil">
                        <a href="{{ route('home') }}#inicio">Inicio</a>
                        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}">Quiénes somos</a>
                        <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-active' : '' }}">Servicios</a>
                        <a href="{{ route('home') }}#resultados">Resultados</a>
                        <a href="{{ route('home') }}#contacto">Contacto</a>
                    </nav>
                    <a href="{{ $whatsappUrl }}" class="btn btn-primary mobile-nav-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        {{ $slot }}

        <footer class="site-footer">
            <div>
                <strong>{{ $settings['clinic_name'] ?? 'Clínica Rejuvenezk' }}</strong>
                <p>{{ $settings['address'] ?? '' }}</p>
            </div>
            <div>
                <p>{{ $settings['phone'] ?? '' }}</p>
                <p>{{ $settings['email'] ?? '' }}</p>
            </div>
        </footer>
    </div>
</body>
</html>
