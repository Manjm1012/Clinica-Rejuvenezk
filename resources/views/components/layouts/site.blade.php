<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($settings['clinic_name'] ?? 'Clínica Rejuvenezk') }}</title>
    <meta name="description" content="Plataforma web comercializable para clínica estética con CRM y WhatsApp integrados.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    @php
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
        $clinicName = $settings['clinic_name'] ?? 'Clínica Rejuvenezk';
        $topbarCtaLabel = $settings['topbar_cta_label'] ?? 'Agenda ahora';
        $whatsappRawUrl = trim((string) ($settings['whatsapp_url'] ?? ''));
        $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
        $hasWhatsappLink = $whatsappRawUrl !== '' || $whatsappDigits !== '';
        $whatsappUrl = $whatsappRawUrl !== ''
            ? $whatsappRawUrl
            : ($whatsappDigits ? 'https://wa.me/' . $whatsappDigits : route('home') . '#contacto');
        $logoSourcePath = $currentClinic?->logo_path ?? $clinic?->logo_path ?? null;
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

        $logoPath = $normalizeMediaPath($logoSourcePath);
        $doctorName = \App\Models\Doctor::query()->where('is_active', true)->orderBy('sort_order')->value('name');
        $brandInitials = collect(preg_split('/\s+/', trim($clinicName)))
            ->filter()
            ->take(2)
            ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
            ->implode('');
    @endphp
    <div class="site-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <a href="{{ route('home') }}#inicio" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                    @if ($logoPath && $publicDisk->exists($logoPath))
                        <span class="brand-logo-shell">
                            <img src="{{ $publicDisk->url($logoPath) }}" alt="Logo de {{ $clinicName }}" class="brand-logo-mark">
                        </span>
                    @else
                        <span class="brand-monogram">{{ $brandInitials ?: 'CR' }}</span>
                    @endif
                    <span class="brand-copy">
                        <span class="brand-clinic-name">{{ $clinicName }}</span>
                        <span class="brand-doctor-name">{{ $doctorName ?: 'Medicina estética avanzada' }}</span>
                    </span>
                </a>

                <nav class="nav-links" aria-label="Navegación principal">
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
                    <a href="{{ route('home') }}#inicio" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Inicio</a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}">Quiénes somos</a>
                    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-active' : '' }}">Servicios</a>
                    <a href="{{ route('home') }}#resultados">Resultados</a>
                    <a href="{{ route('home') }}#contacto">Contacto</a>
                </nav>
                <a href="{{ $whatsappUrl }}" class="btn btn-primary mobile-nav-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
            </div>
        </div>

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
