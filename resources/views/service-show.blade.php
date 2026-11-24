@php($serviceImage = \App\Support\Media::url($service->banner_path) ?? \App\Support\Media::url($service->image_path))
<x-layouts.site
    :settings="$settings"
    :title="$service->name . ' | ' . $settings['clinic_name']"
    :description="$service->short_description ?: strip_tags((string) $service->description)"
    :image="$serviceImage"
>
    <main>
        <section class="container detail-hero">
            <div>
                <p class="kicker">{{ $service->category?->name }}</p>
                @if (!empty($service->meta['subcategory']))
                    <span class="detail-subcategory">{{ $service->meta['subcategory'] }}</span>
                @endif
                <h1>{{ $service->name }}</h1>
                <p>{{ $service->short_description }}</p>
                <div class="hero-actions">
                    <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="btn-primary">Solicitar información</a>
                    <a href="{{ \App\Support\SiteBrand::contactUrl($service->slug) }}" class="btn-secondary">Agendar valoración</a>
                </div>
            </div>
            @if ($serviceImage)
                <figure class="detail-card detail-card-media">
                    <img src="{{ $serviceImage }}" alt="{{ $service->name }}" fetchpriority="high" decoding="async">
                </figure>
            @else
                <div class="detail-card">
                    <p>Cada tratamiento inicia con una valoración médica para definir si es adecuado para ti, la técnica y el número de sesiones.</p>
                </div>
            @endif
        </section>

        <section class="container section-block split-section">
            <article class="rich-copy {{ data_get($service->meta, 'justify_description', true) ? 'rich-copy-justified' : '' }}">{!! $service->description !!}</article>
            <aside class="doctor-box">
                <h3>Contacto rápido</h3>
                @if (! empty($settings['phone']))<p><a href="tel:{{ preg_replace('/[^\d+]/', '', $settings['phone']) }}">{{ $settings['phone'] }}</a></p>@endif
                @if (! empty($settings['email']))<p><a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a></p>@endif
                <p><a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer">Escríbenos por WhatsApp</a></p>
            </aside>
        </section>

        @if ($service->faqs->isNotEmpty())
            <section class="container section-block">
                <div class="section-head">
                    <p class="kicker">Preguntas frecuentes</p>
                    <h2>Antes de tu valoración</h2>
                </div>
                <div class="faq-list">
                    @foreach ($service->faqs as $faq)
                        <details>
                            <summary>{{ $faq->question }}</summary>
                            <p>{{ $faq->answer }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($relatedServices->isNotEmpty())
            <section class="container section-block">
                <div class="section-head">
                    <p class="kicker">Procedimientos relacionados</p>
                    <h2>También puede interesarte</h2>
                </div>
                <div class="card-grid">
                    @foreach ($relatedServices as $related)
                        <article class="service-card">
                            <h3>{{ $related->name }}</h3>
                            <p>{{ $related->short_description }}</p>
                            <a href="{{ route('services.show', $related) }}">Ver detalle</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</x-layouts.site>
