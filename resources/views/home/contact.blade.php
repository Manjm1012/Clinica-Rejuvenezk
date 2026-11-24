{{-- Resumen de contacto al final de la portada. El formulario vive en /contacto (no se duplica aquí). --}}
@php($texts = $page['texts'])
<section id="contacto" class="section section-white cta-wrap">
    <div class="container reveal reveal-2">
        <div class="cta dk-home-contact">
            <div>
                <p class="kicker">{{ $texts['ctaKicker'] }}</p>
                <h2>{{ $texts['ctaTitle'] }}</h2>
                <p>{{ $brand['address'] ?: $texts['ctaBody'] }}</p>
            </div>
            <div class="cta-actions">
                <a class="dk-btn-primary" href="{{ $brand['contactUrl'] }}">Agendar valoración</a>
                @if ($brand['hasWhatsapp'])
                    <a class="btn btn-ghost" href="{{ $brand['bookingUrl'] }}" target="_blank" rel="noopener noreferrer">{{ $texts['ctaWhatsappLabel'] }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
