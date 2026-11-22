@php($imageUrl = \App\Support\Media::url($service->image_path))
<article class="svc-card">
    @if ($imageUrl)
        <figure class="svc-card-media">
            <img src="{{ $imageUrl }}" alt="{{ $service->name }}" loading="lazy" decoding="async">
        </figure>
    @else
        <div class="svc-card-media svc-card-media-empty">
            <span>{{ mb_strtoupper(mb_substr($service->name, 0, 2)) }}</span>
        </div>
    @endif
    <div class="svc-card-copy">
        <h3>{{ $service->name }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($service->short_description ?: 'Tratamiento personalizado con enfoque médico y resultados naturales.', 90) }}</p>
        <div class="svc-card-links dk-svc-actions">
            <a href="{{ route('services.show', $service) }}" class="svc-card-link">Ver detalle</a>
            <a href="{{ $brand['hasWhatsapp'] ? $service->whatsapp_url : '#formulario' }}" class="dk-btn-agendar" @if($brand['hasWhatsapp']) target="_blank" rel="noopener noreferrer" @endif aria-label="Agendar {{ $service->name }}">Agendar</a>
        </div>
    </div>
</article>
