@php
    $clinicName = $settings['clinic_name'] ?? 'Clinica Rejuvenezk';
    $heroTitle = $settings['hero_title'] ?? 'Tu mejor version, con criterio medico y resultados naturales.';
    $heroSubtitle = $settings['hero_subtitle'] ?? 'Protocolos personalizados para rejuvenecimiento facial, armonizacion y bienestar integral.';
    $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
    $whatsappUrl = $whatsappDigits ? 'https://wa.me/' . $whatsappDigits : '#contacto';
    $bannerMedia = null;

    if (!empty($doctor?->photo_path)) {
        $bannerMedia = asset('storage/' . $doctor->photo_path);
    } elseif (!empty($featuredServices->first()?->banner_path)) {
        $bannerMedia = asset('storage/' . $featuredServices->first()->banner_path);
    } elseif (!empty($featuredServices->first()?->image_path)) {
        $bannerMedia = asset('storage/' . $featuredServices->first()->image_path);
    }

    $heroMetrics = $stats->take(3)->map(fn ($stat) => [
        'value' => $stat->value,
        'label' => $stat->label,
    ]);

    if ($heroMetrics->isEmpty()) {
        $heroMetrics = collect([
            ['value' => '+4,800', 'label' => 'Pacientes atendidos'],
            ['value' => '98%', 'label' => 'Satisfaccion global'],
            ['value' => '12 anos', 'label' => 'Experiencia clinica'],
        ]);
    }

    $socialCards = collect([
        ['label' => 'Instagram', 'handle' => '@clinicarejuvenezk', 'text' => 'Antes y despues, reels y tips de cuidado', 'url' => $settings['instagram_url'] ?? null],
        ['label' => 'YouTube', 'handle' => 'Rejuvenezk TV', 'text' => 'Testimonios completos y explicacion medica', 'url' => $settings['youtube_url'] ?? null],
        ['label' => 'WhatsApp', 'handle' => $settings['phone'] ?: 'Agenda inmediata', 'text' => 'Atencion comercial y seguimiento de valoraciones', 'url' => $whatsappDigits ? $whatsappUrl : null],
        ['label' => 'Correo', 'handle' => $settings['email'] ?: 'contacto@rejuvenezk.com', 'text' => 'Solicita informacion detallada y propuestas personalizadas', 'url' => !empty($settings['email']) ? 'mailto:' . $settings['email'] : null],
    ]);

    $testimonialsToShow = $testimonials->take(3);
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body landing-body">
    <div class="ambient ambient-left"></div>
    <div class="ambient ambient-right"></div>

    <header class="topbar reveal reveal-1">
        <div class="container topbar-inner">
            <a href="#inicio" class="brand" aria-label="Ir al inicio de {{ $clinicName }}">{{ $clinicName }}</a>
            <nav class="nav-links" aria-label="Navegacion principal">
                <a href="#servicios">Servicios</a>
                <a href="#resultados">Resultados</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <a href="{{ $whatsappUrl }}" class="btn btn-outline" @if($whatsappDigits) target="_blank" rel="noopener noreferrer" @endif>Agenda ahora</a>
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
                <span>{{ strtoupper(substr($card['label'], 0, 2)) }}</span>
            </a>
        @endforeach
    </aside>

    <main id="inicio">
        <section class="hero section container">
            <div class="hero-copy reveal reveal-2">
                <p class="kicker">Medicina estetica avanzada</p>
                <h1>{{ $heroTitle }}</h1>
                <p class="lead">{{ $heroSubtitle }}</p>
                <div class="hero-actions">
                    <a href="#contacto" class="btn btn-primary">Reservar valoracion</a>
                    <a href="#servicios" class="btn btn-ghost">Ver tratamientos</a>
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
                <div class="hero-card landing-hero-card">
                    <p class="card-kicker">Primera cita</p>
                    <h2>{{ $doctor?->subtitle ?: 'Diagnostico inteligente de piel' }}</h2>
                    <p>{{ $doctor?->bio ?: 'Analisis de calidad cutanea y plan estetico por etapas para resultados armonicos y progresivos.' }}</p>
                </div>
                <div class="hero-stack">
                    <div class="mini-card">
                        <span>Protocolo estrella</span>
                        <strong>{{ $featuredServices->first()?->name ?: 'Lift & Glow 360' }}</strong>
                    </div>
                    <div class="mini-card">
                        <span>Especialista</span>
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
                        <span class="kicker">Experiencia premium</span>
                        <strong>{{ $clinicName }}</strong>
                    </figcaption>
                </figure>
            @endif
        </section>

        <section id="servicios" class="section section-soft">
            <div class="container reveal reveal-2">
                <p class="kicker">Nuestros tratamientos</p>
                <h2 class="section-title">Tecnologia, precision y naturalidad</h2>
                <div class="services-grid">
                    @foreach ($services->take(6) as $service)
                        <article class="service-card landing-service-card">
                            @if ($service->image_path)
                                <figure class="media-placeholder media-placeholder-service service-media">
                                    <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->name }}" loading="lazy">
                                </figure>
                            @else
                                <figure class="media-placeholder media-placeholder-service service-media service-fallback">
                                    <span>{{ $service->category?->name ?: 'Procedimiento' }}</span>
                                </figure>
                            @endif
                            <h3>{{ $service->name }}</h3>
                            <p>{{ $service->short_description ?: 'Tratamiento personalizado con enfoque medico, seguridad y resultados naturales.' }}</p>
                            <div class="service-links">
                                <a href="{{ route('services.show', $service) }}">Ver detalle</a>
                                <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="resultados" class="section container">
            <div class="reveal reveal-2">
                <p class="kicker">Testimonios</p>
                <h2 class="section-title">Pacientes que ya viven su cambio</h2>
            </div>
            <div class="testimonials-grid reveal reveal-3">
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

        <section id="redes" class="section section-soft social-section">
            <div class="container reveal reveal-2">
                <p class="kicker">Comunidad {{ $clinicName }}</p>
                <h2 class="section-title">Conecta con nuestras redes y canales</h2>
                <p class="lead social-lead">Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.</p>

                <div class="social-grid">
                    @foreach ($socialCards as $card)
                        <a class="social-card" href="{{ $card['url'] ?: '#contacto' }}" @if($card['url']) target="_blank" rel="noopener noreferrer" @endif aria-label="Ir a {{ $card['label'] }}">
                            <span class="social-card-icon" aria-hidden="true">{{ strtoupper(substr($card['label'], 0, 1)) }}</span>
                            <strong>{{ $card['label'] }}</strong>
                            <span>{{ $card['handle'] }}</span>
                            <small>{{ $card['text'] }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contacto" class="section cta-wrap">
            <div class="container contact-layout reveal reveal-2">
                <div class="cta">
                    <div>
                        <p class="kicker">Agenda tu cita</p>
                        <h2>Empieza hoy tu protocolo personalizado.</h2>
                        <p>{{ $settings['address'] ?: 'Atencion presencial y seguimiento digital para una experiencia comercial ordenada.' }}</p>
                    </div>
                    <div class="cta-actions">
                        <a class="btn btn-primary" href="{{ $whatsappUrl }}" @if($whatsappDigits) target="_blank" rel="noopener noreferrer" @endif>WhatsApp</a>
                        <a class="btn btn-ghost" href="{{ !empty($settings['email']) ? 'mailto:' . $settings['email'] : '#formulario' }}">{{ $settings['email'] ?: 'Solicitar informacion' }}</a>
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
            <p>{{ $clinicName }}</p>
            <p>{{ $settings['phone'] ?: 'Atencion comercial premium' }}</p>
        </div>
    </footer>
</body>
</html>
