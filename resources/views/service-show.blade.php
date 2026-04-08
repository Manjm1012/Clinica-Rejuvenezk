<x-layouts.site :settings="$settings" :title="$service->name . ' | ' . $settings['clinic_name']">
    <main>
        <section class="detail-hero">
            <div>
                <span class="eyebrow">{{ $service->category?->name }}</span>
                @if (!empty($service->meta['subcategory']))
                    <span class="detail-subcategory">{{ $service->meta['subcategory'] }}</span>
                @endif
                <h1>{{ $service->name }}</h1>
                <p>{{ $service->short_description }}</p>
                <div class="hero-actions">
                    <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noreferrer" class="btn-primary">Solicitar información</a>
                    <a href="{{ route('home') }}#contacto" class="btn-secondary">Agendar valoración</a>
                </div>
            </div>
            <div class="detail-card">
                <p>Panel y contenidos editables desde Filament, listos para replicar en otros clientes.</p>
            </div>
        </section>

        <section class="section-block split-section">
            <article class="rich-copy">{!! $service->description !!}</article>
            <aside class="doctor-box">
                <h3>Contacto rápido</h3>
                <p>{{ $settings['phone'] }}</p>
                <p>{{ $settings['email'] }}</p>
            </aside>
        </section>

        @if ($service->faqs->isNotEmpty())
            <section class="section-block">
                <div class="section-head">
                    <span class="eyebrow">Preguntas frecuentes</span>
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
            <section class="section-block">
                <div class="section-head">
                    <span class="eyebrow">Procedimientos relacionados</span>
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
