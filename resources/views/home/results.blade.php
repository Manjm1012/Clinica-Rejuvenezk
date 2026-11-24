{{--
    Resultados: videos, galería y testimonios.
    Solo se muestran testimonios reales y verificables (con source_url) cargados en el panel (nada inventado). El promedio solo
    aparece con suficientes reseñas verificables (Testimonial::publicRating): en servicios de salud
    un testimonio ficticio o un "5,0/5" con una sola opinión es publicidad engañosa.

    Opcionales: $sectionTitle (h2 propio) y $showKicker (false en la página de Resultados,
    donde el encabezado de la página ya dice "Resultados").
--}}
@php
    $texts = $page['texts'];
    $realTestimonials = $testimonials->take($testimonialsLimit ?? 3);
    $rating = \App\Models\Testimonial::publicRating();
    $videos = $videos ?? $page['videos'];
    $gallery = $gallery ?? $page['gallery'];
@endphp

@if ($videos->isNotEmpty() || $gallery->isNotEmpty() || $realTestimonials->isNotEmpty())
<section id="resultados" class="section section-white">
    <div class="container reveal reveal-2">
        @if ($showKicker ?? true)
            <p class="kicker">{{ $texts['testimonialsKicker'] }}</p>
        @endif
        <h2 class="section-title">{{ $sectionTitle ?? $texts['testimonialsTitle'] }}</h2>
        @if ($rating)
            <div class="testimonials-overview">
                <div class="testimonials-pill">
                    <strong>Valoración {{ $rating['average'] }}/5</strong>
                    <span>{{ $rating['count'] }} opiniones verificables de pacientes</span>
                </div>
            </div>
        @endif
    </div>

    @if ($videos->isNotEmpty())
        <div class="container results-video-band reveal reveal-2">
            <div class="results-video-carousel" id="videoResultsCarousel" aria-label="Videos de resultados de {{ $brand['clinicName'] }}">
                @foreach ($videos as $video)
                    <article class="results-video-card">
                        <div class="results-video-media">
                            @if ($video['is_iframe'])
                                <iframe src="{{ $video['url'] }}" title="{{ $video['title'] }}" loading="lazy"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen></iframe>
                            @else
                                <video controls preload="metadata" playsinline poster="{{ $video['poster'] ?? '' }}">
                                    <source src="{{ $video['url'] }}">
                                </video>
                                <button type="button" class="results-video-play-overlay" aria-label="Reproducir video {{ $video['title'] }}">
                                    <span class="results-video-play-badge" aria-hidden="true"></span>
                                    <span class="results-video-play-copy">Reproducir</span>
                                </button>
                                <div class="results-video-player-controls" aria-hidden="true">
                                    <button type="button" class="results-video-toggle" aria-label="Alternar reproducción"></button>
                                    <button type="button" class="results-video-fullscreen" aria-label="Pantalla completa"></button>
                                </div>
                            @endif
                        </div>
                        <div class="results-video-meta">
                            <strong>{{ $video['title'] }}</strong>
                            <span>{{ $video['label'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="result-visual-dots" id="videoResultsDots" aria-hidden="true"></div>
        </div>
    @endif

    @if ($gallery->isNotEmpty())
        <div class="container result-visual-band reveal reveal-2">
            <article class="result-visual-copy">
                <p class="kicker">Galería visual</p>
                <h3>{{ $texts['resultsTitle'] }}</h3>
                <p>{{ $texts['resultsLead'] }}</p>
                @if (! request()->routeIs('results'))
                    <a href="{{ route('results') }}" class="svc-card-link">Ver todos los resultados →</a>
                @else
                    <span class="result-visual-hint">Desliza para ver más casos</span>
                @endif
            </article>

            <div class="result-visual-grid" id="resultCarousel" aria-label="Galería visual de {{ $brand['clinicName'] }}">
                @foreach ($gallery as $item)
                    <figure class="result-visual-card">
                        <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}" loading="lazy" decoding="async">
                        <figcaption>
                            <strong>{{ $item['title'] }}</strong>
                            <span>{{ $item['label'] }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            <div class="result-visual-dots" id="resultDots" aria-hidden="true"></div>
        </div>
    @endif

    @if ($realTestimonials->isNotEmpty())
        <div class="container testimonials-grid reveal reveal-3">
            @foreach ($realTestimonials as $testimonial)
                <blockquote class="quote-card">
                    <div class="quote-meta">
                        <span class="quote-avatar">{{ mb_strtoupper(mb_substr($testimonial->patient_name ?: 'P', 0, 2)) }}</span>
                        <div class="quote-author">
                            <strong>{{ $testimonial->patient_name }}</strong>
                            <small>{{ $testimonial->reviewed_at ? 'Paciente '.$testimonial->reviewed_at->format('Y') : 'Paciente' }}</small>
                        </div>
                    </div>
                    <div class="testimonial-rating" aria-label="{{ (int) $testimonial->rating }} de 5 estrellas">{{ str_repeat('★', max(1, min(5, (int) $testimonial->rating))) }}</div>
                    <p>“{{ $testimonial->content }}”</p>
                    {{-- Solo llegan reseñas verificables (Testimonial::published): siempre con enlace a la original. --}}
                    <cite><a href="{{ $testimonial->source_url }}" target="_blank" rel="noopener nofollow">Ver opinión original{{ $testimonial->source ? ' en '.$testimonial->source : '' }}</a></cite>
                </blockquote>
            @endforeach
        </div>
    @endif
</section>
@endif
