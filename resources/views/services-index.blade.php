@php($clinicName = $settings['clinic_name'] ?? 'Dr. Kevin Rejuvenezk')

<x-layouts.site :settings="$settings" :title="'Tratamientos | ' . $clinicName" :description="'Catálogo de tratamientos faciales y corporales de ' . $clinicName . ': valoración médica, tecnología y resultados naturales.'">
    <main>
        <x-page-header kicker="Catálogo de tratamientos" title="Explora todas nuestras categorías y procedimientos">
            Un recorrido completo por las soluciones faciales, corporales y médicas disponibles: {{ $serviceCategories->count() }} categorías y {{ $serviceCategories->sum(fn ($category) => $category->services->count()) }} tratamientos.
        </x-page-header>

        @foreach ($serviceCategories as $category)
            <section id="cat-{{ $category->slug ?: $category->id }}" class="container dk-page-body">
                <div class="section-head services-catalog-head">
                    <div>
                        <p class="kicker">Categoría</p>
                        <h2>{{ $category->name }}</h2>
                    </div>
                    <p>{{ $category->description ?: 'Selección completa de procedimientos y alternativas terapéuticas dentro de esta categoría.' }}</p>
                </div>

                <div class="services-grid services-grid-catalog">
                    @foreach ($category->services as $service)
                        <article class="landing-service-card">
                            @include('partials.service-media', ['service' => $service])
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