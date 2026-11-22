@php($clinicName = $settings['clinic_name'] ?? 'Dr. Kevin Rejuvenezk')

<x-layouts.site :settings="$settings" :title="'Tratamientos | ' . $clinicName" :description="'Catálogo de tratamientos faciales y corporales de ' . $clinicName . ': valoración médica, tecnología y resultados naturales.'">
    <main class="services-catalog-page">
        <section class="detail-hero services-catalog-hero">
            <div>
                <span class="eyebrow">Catálogo de tratamientos</span>
                <h1>Explora todas nuestras categorías y procedimientos</h1>
                <p>Un recorrido completo por las soluciones faciales, corporales y médicas disponibles, organizado para ayudarte a comparar con claridad.</p>
            </div>
            <div class="detail-card">
                <p>{{ $serviceCategories->count() }} categorías activas y {{ $serviceCategories->sum(fn ($category) => $category->services->count()) }} tratamientos publicados.</p>
            </div>
        </section>

        @foreach ($serviceCategories as $category)
            <section id="cat-{{ $category->slug ?: $category->id }}" class="section-block services-catalog-block">
                <div class="section-head services-catalog-head">
                    <div>
                        <span class="eyebrow">Categoría</span>
                        <h2>{{ $category->name }}</h2>
                    </div>
                    <p>{{ $category->description ?: 'Selección completa de procedimientos y alternativas terapéuticas dentro de esta categoría.' }}</p>
                </div>

                <div class="services-grid services-grid-catalog">
                    @foreach ($category->services as $service)
                        @php($serviceImageUrl = \App\Support\Media::url($service->image_path))
                        <article class="landing-service-card">
                            @if ($serviceImageUrl)
                                <figure class="media-placeholder media-placeholder-service service-media">
                                    <img src="{{ $serviceImageUrl }}" alt="{{ $service->name }}" loading="lazy" decoding="async">
                                </figure>
                            @else
                                <figure class="media-placeholder media-placeholder-service service-media service-media-empty" aria-hidden="true">
                                    <span>{{ mb_strtoupper(mb_substr($service->name, 0, 2)) }}</span>
                                </figure>
                            @endif
                            <div class="service-card-copy">
                                <h3>{{ $service->name }}</h3>
                                <p>{{ $service->short_description ?: 'Tratamiento personalizado con enfoque médico, seguridad y resultados naturales.' }}</p>
                                <div class="service-links">
                                    <a href="{{ route('services.show', $service) }}">Ver detalle</a>
                                    <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>
</x-layouts.site>