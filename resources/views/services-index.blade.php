@php
    $clinicName = $settings['clinic_name'] ?? 'Clínica Rejuvenezk';
    $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
    $normalizeMediaPath = function (?string $path): ?string {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = trim($path);

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            $normalized = parse_url($normalized, PHP_URL_PATH) ?: $normalized;
        }

        $normalized = ltrim(rawurldecode($normalized), '/');

        foreach (['media/', 'storage/', 'public/', 'app/public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return $normalized !== '' ? $normalized : null;
    };
@endphp

<x-layouts.site :settings="$settings" :title="'Tratamientos | ' . $clinicName">
    <main class="site-shell services-catalog-page">
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
                        @php($serviceImagePath = $normalizeMediaPath($service->image_path))
                        <article class="landing-service-card">
                            @if ($serviceImagePath && $publicDisk->exists($serviceImagePath))
                                <figure class="media-placeholder media-placeholder-service service-media">
                                    <img src="{{ $publicDisk->url($serviceImagePath) }}" alt="{{ $service->name }}" loading="lazy">
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