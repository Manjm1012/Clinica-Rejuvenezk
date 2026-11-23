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
                <div class="svc-carousel-wrap">
                    <div class="svc-carousel" aria-label="Tratamientos de {{ $panel['name'] }}">
                        @foreach ($panel['services'] as $service)
                            @include('home.service-card', ['service' => $service])
                        @endforeach

                        <article class="svc-card svc-card-cta">
                            <div class="svc-card-cta-inner">
                                <strong>¿No encuentras lo que buscas?</strong>
                                <p>Agenda una valoración y diseñamos un protocolo a tu medida.</p>
                                <a href="{{ $brand['bookingUrl'] }}" class="btn btn-primary" @if($brand['hasWhatsapp']) target="_blank" rel="noopener noreferrer" @endif>WhatsApp</a>
                                <a href="{{ route('services.index') }}" class="btn btn-ghost">Ver catálogo completo</a>
                            </div>
                        </article>
                    </div>
                    <div class="svc-carousel-hint" aria-hidden="true">Desliza para ver todos los tratamientos →</div>
                </div>
            </div>
        @endforeach
    </div>
</section>
