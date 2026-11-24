{{-- Banners promocionales (editables en Configuración → Banners Home).
     Escritorio: texto sobre la imagen y flechas a los lados. Móvil: texto debajo de la imagen,
     sin flechas (deslizar + puntos). Los puntos van siempre fuera del bloque de texto.
     Teclado (← →), deslizamiento y reproducción automática en resources/js/app.js. --}}
@php($banners = $page['promoBanners']->filter(fn ($banner) => ! empty($banner['image']))->values())
@if ($banners->isNotEmpty())
    <section class="banners-carousel-wrap section dk-promos">
        <div class="banners-carousel" role="region" aria-roledescription="carrusel" aria-label="Banners promocionales"
             @if ($banners->count() > 1) tabindex="0" @endif>
            <div class="banners-carousel-inner">
                @foreach ($banners as $index => $banner)
                    <article class="banner-slide tone-{{ $index % 3 }}" data-slide="{{ $index }}"
                             role="group" aria-roledescription="diapositiva" aria-label="{{ $index + 1 }} de {{ $banners->count() }}: {{ $banner['title'] }}"
                             @if ($index > 0) aria-hidden="true" @endif>
                        <div class="banner-slide-media">
                            <img src="{{ $banner['image'] }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        </div>
                        <div class="banner-slide-overlay">
                            <span>{{ $banner['kicker'] }}</span>
                            <strong>{{ $banner['title'] }}</strong>
                            <p>{{ $banner['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($banners->count() > 1)
                <button type="button" class="banner-control banner-prev" aria-label="Banner anterior"><span aria-hidden="true">‹</span></button>
                <button type="button" class="banner-control banner-next" aria-label="Banner siguiente"><span aria-hidden="true">›</span></button>
                <div class="banner-indicators">
                    @foreach ($banners as $index => $banner)
                        <button type="button" class="banner-indicator{{ $index === 0 ? ' active' : '' }}" data-index="{{ $index }}" aria-label="Ir a banner {{ $index + 1 }}" @if($index === 0) aria-current="true" @endif></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
