{{--
    Imagen de un tratamiento (tarjetas de la portada y del catálogo).
    Los afiches son verticales y traen el texto grabado arriba: se recortan a 4:5 desde
    arriba (object-position: center top) para que ese texto nunca quede fuera.
    Si el archivo no existe, se muestra un marcador con las iniciales.
--}}
@php($imageUrl = \App\Support\Media::url($service->image_path))
<figure class="dk-svc-media">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="Afiche del tratamiento {{ $service->name }}" width="800" height="1000" loading="lazy" decoding="async">
    @else
        <div class="dk-svc-media-empty" aria-hidden="true">
            <span class="dk-svc-media-mono">{{ \App\Support\Media::initials($service->name) }}</span>
            <span class="dk-svc-media-name">{{ $service->name }}</span>
        </div>
    @endif
</figure>
