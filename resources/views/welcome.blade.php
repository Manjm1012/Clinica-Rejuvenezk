<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Clinica Rejuvenezk | Medicina Estetica Premium</title>
    <meta name="description" content="Clinica Rejuvenezk: tratamientos faciales y corporales con tecnologia avanzada y enfoque medico personalizado.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <div class="ambient ambient-left"></div>
    <div class="ambient ambient-right"></div>

    <header class="topbar reveal reveal-1">
        <div class="container topbar-inner">
            <a href="#inicio" class="brand" aria-label="Ir al inicio de Clinica Rejuvenezk">Rejuvenezk</a>
            <nav class="nav-links" aria-label="Navegacion principal">
                <a href="#servicios">Servicios</a>
                <a href="#resultados">Resultados</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <a href="#contacto" class="btn btn-outline">Agenda ahora</a>
        </div>
    </header>

    <aside class="social-dock" aria-label="Redes sociales de Clinica Rejuvenezk">
        <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.9A3.85 3.85 0 0 0 3.9 7.75v8.5a3.85 3.85 0 0 0 3.85 3.85h8.5a3.85 3.85 0 0 0 3.85-3.85v-8.5a3.85 3.85 0 0 0-3.85-3.85h-8.5Zm8.9 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9Z" />
            </svg>
        </a>
        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.5 1.7-1.5h1.6V5c-.8-.1-1.7-.2-2.6-.2-2.6 0-4.2 1.6-4.2 4.4V11H8v3h2.5v8h3Z" />
            </svg>
        </a>
        <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14.9 3c.2 1.8 1.2 3.4 2.8 4.3 1 .6 2.1.9 3.3.9v3.1c-1.4 0-2.7-.3-3.9-.9-.8-.4-1.5-.9-2.2-1.5v6.1c0 3.4-2.7 6-6.1 6a6 6 0 0 1-6-6c0-3.4 2.7-6.1 6-6.1.3 0 .6 0 .9.1v3.1a3 3 0 1 0 2.2 2.9V3h3Z" />
            </svg>
        </a>
        <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M22 12s0-3-.4-4.4a3.1 3.1 0 0 0-2.2-2.2C18 5 12 5 12 5s-6 0-7.4.4a3.1 3.1 0 0 0-2.2 2.2C2 9 2 12 2 12s0 3 .4 4.4a3.1 3.1 0 0 0 2.2 2.2C6 19 12 19 12 19s6 0 7.4-.4a3.1 3.1 0 0 0 2.2-2.2C22 15 22 12 22 12Zm-12.3 3.5V8.5l5.8 3.5-5.8 3.5Z" />
            </svg>
        </a>
    </aside>

    <main id="inicio">
        <section class="hero section container">
            <div class="hero-copy reveal reveal-2">
                <p class="kicker">Medicina estetica avanzada</p>
                <h1>Tu mejor version, con criterio medico y resultados naturales.</h1>
                <p class="lead">
                    Protocolos personalizados para rejuvenecimiento facial, armonizacion y bienestar integral.
                    Tratamos piel, volumen y textura con una experiencia premium desde la primera consulta.
                </p>
                <div class="hero-actions">
                    <a href="#contacto" class="btn btn-primary">Reservar valoracion</a>
                    <a href="#servicios" class="btn btn-ghost">Ver tratamientos</a>
                </div>
                <div class="hero-metrics">
                    <article>
                        <strong>+4,800</strong>
                        <span>Pacientes atendidos</span>
                    </article>
                    <article>
                        <strong>98%</strong>
                        <span>Satisfaccion global</span>
                    </article>
                    <article>
                        <strong>12 anos</strong>
                        <span>Experiencia clinica</span>
                    </article>
                </div>
            </div>

            <aside class="hero-panel reveal reveal-3" aria-label="Resumen de experiencia Rejuvenezk">
                <div class="hero-card">
                    <p class="card-kicker">Primera cita</p>
                    <h2>Diagnostico inteligente de piel</h2>
                    <p>Analisis de calidad cutanea y plan estetico por etapas para resultados armonicos y progresivos.</p>
                </div>
                <div class="hero-stack">
                    <div class="mini-card">
                        <span>Protocolo estrella</span>
                        <strong>Lift & Glow 360</strong>
                    </div>
                    <div class="mini-card">
                        <span>Tiempo promedio</span>
                        <strong>45 min por sesion</strong>
                    </div>
                </div>
            </aside>
        </section>

        <section class="container banner-slot reveal reveal-2" aria-label="Espacio para imagen principal debajo del banner superior">
            <figure class="media-placeholder media-placeholder-lg media-banner">
                <img src="{{ asset('images/banner/banner-principal.jpg') }}" alt="Banner principal de Clinica Rejuvenezk" loading="lazy">
            </figure>
        </section>

        <section id="servicios" class="section section-soft">
            <div class="container reveal reveal-2">
                <p class="kicker">Nuestros tratamientos</p>
                <h2 class="section-title">Tecnologia, precision y naturalidad</h2>
                <div class="services-grid">
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/armonizacion-facial.jpg') }}" alt="Servicio de armonizacion facial" loading="lazy">
                        </figure>
                        <h3>Armonizacion facial</h3>
                        <p>Rellenos estrategicos para balancear rasgos sin perder expresion.</p>
                    </article>
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/toxina-botulinica.jpg') }}" alt="Servicio de toxina botulinica" loading="lazy">
                        </figure>
                        <h3>Toxina botulinica</h3>
                        <p>Suaviza lineas de expresion preservando movimiento natural.</p>
                    </article>
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/bioestimuladores.jpg') }}" alt="Servicio de bioestimuladores" loading="lazy">
                        </figure>
                        <h3>Bioestimuladores</h3>
                        <p>Activa colageno para mejorar firmeza y densidad de la piel.</p>
                    </article>
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/skin-quality.jpg') }}" alt="Servicio de skin quality" loading="lazy">
                        </figure>
                        <h3>Skin quality</h3>
                        <p>Hidratacion profunda y luminosidad con protocolos combinados.</p>
                    </article>
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/remodelacion-corporal.jpg') }}" alt="Servicio de remodelacion corporal" loading="lazy">
                        </figure>
                        <h3>Remodelacion corporal</h3>
                        <p>Tratamientos no invasivos para contorno y textura.</p>
                    </article>
                    <article class="service-card">
                        <figure class="media-placeholder media-placeholder-service service-media">
                            <img src="{{ asset('images/services/medicina-regenerativa.jpg') }}" alt="Servicio de medicina regenerativa" loading="lazy">
                        </figure>
                        <h3>Medicina regenerativa</h3>
                        <p>Procedimientos enfocados en restaurar y potenciar tejido.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="resultados" class="section container">
            <div class="reveal reveal-2">
                <p class="kicker">Testimonios</p>
                <h2 class="section-title">Pacientes que ya viven su cambio</h2>
            </div>
            <div class="testimonials-grid reveal reveal-3">
                <blockquote class="quote-card">
                    <figure class="media-placeholder media-placeholder-video">
                        <video class="testimonial-video" controls preload="metadata" playsinline>
                            <source src="{{ asset('videos/testimonios/testimonio-1.mp4') }}" type="video/mp4">
                            Tu navegador no soporta video HTML5.
                        </video>
                    </figure>
                    "Me encanto porque respetaron mis facciones y me explicaron cada paso. Me veo fresca, no cambiada."
                    <cite>Ana M. | 34 anos</cite>
                </blockquote>
                <blockquote class="quote-card">
                    <figure class="media-placeholder media-placeholder-video">
                        <video class="testimonial-video" controls preload="metadata" playsinline>
                            <source src="{{ asset('videos/testimonios/testimonio-2.mp4') }}" type="video/mp4">
                            Tu navegador no soporta video HTML5.
                        </video>
                    </figure>
                    "El trato es impecable y los resultados se notan desde la primera sesion. Volvere sin duda."
                    <cite>Carla R. | 41 anos</cite>
                </blockquote>
                <blockquote class="quote-card">
                    <figure class="media-placeholder media-placeholder-video">
                        <video class="testimonial-video" controls preload="metadata" playsinline>
                            <source src="{{ asset('videos/testimonios/testimonio-3.mp4') }}" type="video/mp4">
                            Tu navegador no soporta video HTML5.
                        </video>
                    </figure>
                    "Senti confianza total por el enfoque medico. El plan fue claro y super personalizado."
                    <cite>Daniela P. | 29 anos</cite>
                </blockquote>
            </div>
        </section>

        <section id="redes" class="section section-soft social-section">
            <div class="container reveal reveal-2">
                <p class="kicker">Comunidad Rejuvenezk</p>
                <h2 class="section-title">Conecta con nuestras redes sociales</h2>
                <p class="lead social-lead">Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.</p>

                <div class="social-grid">
                    <a class="social-card" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar Instagram">
                        <span class="social-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.9A3.85 3.85 0 0 0 3.9 7.75v8.5a3.85 3.85 0 0 0 3.85 3.85h8.5a3.85 3.85 0 0 0 3.85-3.85v-8.5a3.85 3.85 0 0 0-3.85-3.85h-8.5Zm8.9 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9Z" />
                            </svg>
                        </span>
                        <strong>Instagram</strong>
                        <span>@clinicarejuvenezk</span>
                        <small>Antes y despues, reels y tips de cuidado</small>
                    </a>
                    <a class="social-card" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar Facebook">
                        <span class="social-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.5 1.7-1.5h1.6V5c-.8-.1-1.7-.2-2.6-.2-2.6 0-4.2 1.6-4.2 4.4V11H8v3h2.5v8h3Z" />
                            </svg>
                        </span>
                        <strong>Facebook</strong>
                        <span>Clinica Rejuvenezk</span>
                        <small>Novedades, eventos y promociones especiales</small>
                    </a>
                    <a class="social-card" href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar TikTok">
                        <span class="social-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M14.9 3c.2 1.8 1.2 3.4 2.8 4.3 1 .6 2.1.9 3.3.9v3.1c-1.4 0-2.7-.3-3.9-.9-.8-.4-1.5-.9-2.2-1.5v6.1c0 3.4-2.7 6-6.1 6a6 6 0 0 1-6-6c0-3.4 2.7-6.1 6-6.1.3 0 .6 0 .9.1v3.1a3 3 0 1 0 2.2 2.9V3h3Z" />
                            </svg>
                        </span>
                        <strong>TikTok</strong>
                        <span>@rejuvenezk</span>
                        <small>Videos de procedimientos y contenido educativo</small>
                    </a>
                    <a class="social-card" href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar YouTube">
                        <span class="social-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M22 12s0-3-.4-4.4a3.1 3.1 0 0 0-2.2-2.2C18 5 12 5 12 5s-6 0-7.4.4a3.1 3.1 0 0 0-2.2 2.2C2 9 2 12 2 12s0 3 .4 4.4a3.1 3.1 0 0 0 2.2 2.2C6 19 12 19 12 19s6 0 7.4-.4a3.1 3.1 0 0 0 2.2-2.2C22 15 22 12 22 12Zm-12.3 3.5V8.5l5.8 3.5-5.8 3.5Z" />
                            </svg>
                        </span>
                        <strong>YouTube</strong>
                        <span>Rejuvenezk TV</span>
                        <small>Testimonios completos y explicacion medica</small>
                    </a>
                </div>
            </div>
        </section>

        <section id="contacto" class="section cta-wrap">
            <div class="container cta reveal reveal-2">
                <div>
                    <p class="kicker">Agenda tu cita</p>
                    <h2>Empieza hoy tu protocolo personalizado.</h2>
                    <p>Atencion en Bogota, Medellin y consultas de seguimiento online.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn-primary" href="https://wa.me/573001112233" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="btn btn-ghost" href="mailto:contacto@rejuvenezk.com">contacto@rejuvenezk.com</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <p>Clinica Rejuvenezk</p>
            <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }}</p>
        </div>
    </footer>
</body>
</html>
