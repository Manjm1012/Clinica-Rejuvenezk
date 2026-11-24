<article class="svc-card">
    @include('partials.service-media', ['service' => $service])
    <div class="svc-card-copy">
        <h3>{{ $service->name }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($service->short_description ?: 'Tratamiento personalizado con enfoque médico y resultados naturales.', 90) }}</p>
        <div class="svc-card-links dk-svc-actions">
            <a href="{{ route('services.show', $service) }}" class="svc-card-link">Ver detalle</a>
            <a href="{{ \App\Support\SiteBrand::contactUrl($service->slug) }}" class="dk-btn-agendar" aria-label="Agendar {{ $service->name }}">Agendar</a>
        </div>
    </div>
</article>
