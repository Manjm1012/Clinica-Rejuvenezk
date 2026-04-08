<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($settings['clinic_name'] ?? 'Clínica Rejuvenezk') }}</title>
    <meta name="description" content="Plataforma web comercializable para clínica estética con CRM y WhatsApp integrados.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <div class="site-shell">
        <header class="topbar">
            <a href="{{ route('home') }}" class="brand-mark">
                @if (!empty($currentClinic?->logo_path))
                    <img src="{{ asset('storage/' . $currentClinic->logo_path) }}" alt="Logo {{ $settings['clinic_name'] ?? 'Clínica' }}" class="brand-logo">
                @else
                    {{ $settings['clinic_name'] ?? 'Clínica Rejuvenezk' }}
                @endif
            </a>
            <nav class="topnav">
                <a href="#procedimientos">Procedimientos</a>
                <a href="#doctor">Especialista</a>
                <a href="#testimonios">Testimonios</a>
                <a href="#contacto">Contacto</a>
                <a href="{{ !empty($settings['whatsapp_url']) ? $settings['whatsapp_url'] : ('https://wa.me/' . preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '')) }}" class="cta-nav" target="_blank" rel="noreferrer">WhatsApp</a>
            </nav>
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
