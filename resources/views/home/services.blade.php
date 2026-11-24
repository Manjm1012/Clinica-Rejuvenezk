@php
    $texts = $page['texts'];
    // Una pestaña por categoría; si no hay categorías, los destacados en un solo panel.
    $panels = $serviceCategories->isNotEmpty()
        ? $serviceCategories->map(fn ($category) => ['id' => 'cat-'.$category->id, 'name' => $category->name, 'services' => $category->services])
        : collect([['id' => 'cat-destacados', 'name' => 'Tratamientos', 'services' => $featuredServices]]);
@endphp
<section id="servicios" class="section section-soft section-beige">
    <div class="container reveal reveal-2">
        <div class="services-head">
            <div>
                <p class="kicker">{{ $texts['servicesKicker'] }}</p>
                <h2 class="section-title">{{ $texts['servicesTitle'] }}</h2>
            </div>
            <p class="section-intro service-intro">{{ $texts['servicesLead'] }}</p>
        </div>

        <nav class="dk-svc-shortcuts" aria-label="Accesos rápidos a tratamientos">
            <a href="{{ route('services.index', ['categoria' => 'facial']) }}#cat-facial">Tratamientos faciales <span aria-hidden="true">→</span></a>
            <a href="{{ route('services.index', ['categoria' => 'corporal']) }}#cat-corporal">Tratamientos corporales <span aria-hidden="true">→</span></a>
            <a href="{{ route('services.index') }}">Ver catálogo completo <span aria-hidden="true">→</span></a>
        </nav>

        @if ($panels->count() > 1)
            <div class="services-tabs" role="tablist" aria-label="Categorías de tratamientos">
                @foreach ($panels as $i => $panel)
                    <button class="services-tab {{ $i === 0 ? 'is-active' : '' }}" role="tab" type="button"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="{{ $panel['id'] }}" data-tab="{{ $panel['id'] }}">{{ $panel['name'] }}</button>
                @endforeach
            </div>
        @endif

        @foreach ($panels as $i => $panel)
            <div class="services-panel {{ $i === 0 ? 'is-active' : '' }}" id="{{ $panel['id'] }}" role="tabpanel">
                {{-- Carrusel: flechas (desktop), deslizamiento táctil, teclado y barra de progreso. JS en resources/js/app.js --}}
                <div class="svc-carousel-wrap" data-carousel>
                    <button type="button" class="dk-carousel-btn dk-carousel-prev" data-carousel-prev aria-controls="{{ $panel['id'] }}-track" aria-label="Ver tratamientos anteriores de {{ $panel['name'] }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" class="dk-carousel-btn dk-carousel-next" data-carousel-next aria-controls="{{ $panel['id'] }}-track" aria-label="Ver más tratamientos de {{ $panel['name'] }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="svc-carousel" id="{{ $panel['id'] }}-track" data-carousel-track tabindex="0" role="region" aria-roledescription="carrusel" aria-label="Tratamientos de {{ $panel['name'] }}. Usa las flechas del teclado para desplazarte.">
                        @foreach ($panel['services'] as $service)
                            @include('home.service-card', ['service' => $service])
                        @endforeach

                        <article class="svc-card svc-card-cta">
                            <div class="svc-card-cta-inner">
                                <strong>¿No encuentras lo que buscas?</strong>
                                <p>Agenda una valoración y diseñamos un protocolo a tu medida.</p>
                                <a href="{{ $brand['contactUrl'] }}" class="btn btn-primary">Agendar valoración</a>
                                <a href="{{ route('services.index') }}" class="btn btn-ghost">Ver catálogo completo</a>
                            </div>
                        </article>
                    </div>
                    <div class="dk-carousel-progress" aria-hidden="true"><span data-carousel-thumb></span></div>
                </div>
            </div>
        @endforeach
    </div>
</section>
