@php($hero = $page['hero'])
<section class="dk-hero" aria-labelledby="dk-hero-title">
    <div class="container dk-hero-grid">
        <div class="dk-hero-copy">
            <p class="kicker dk-hero-kicker">{{ $hero['kicker'] }}</p>
            <h1 id="dk-hero-title" class="dk-hero-title">{{ $hero['title'] }}</h1>
            <p class="dk-hero-subtitle">{{ $hero['subtitle'] }}</p>

            <div class="dk-hero-actions">
                <a href="#formulario" class="dk-btn-primary">{{ $hero['primaryCta'] }}</a>
                <a href="{{ $brand['bookingUrl'] }}" class="dk-hero-wa" @if($brand['hasWhatsapp']) target="_blank" rel="noopener noreferrer" @endif>
                    @if ($brand['hasWhatsapp'])
                        <span class="dk-hero-wa-icon"><x-site-icon name="whatsapp" /></span>
                        Escríbenos por WhatsApp
                    @else
                        Déjanos tus datos y te llamamos
                    @endif
                </a>
            </div>

            <a href="{{ route('team') }}" class="dk-doctor-chip">
                <span class="dk-doctor-chip-avatar" aria-hidden="true">
                    @if ($page['doctorPhotoUrl'])
                        <img src="{{ $page['doctorPhotoUrl'] }}" alt="" loading="lazy" decoding="async">
                    @else
                        {{ $brand['initials'] }}
                    @endif
                </span>
                <span class="dk-doctor-chip-copy">
                    <strong>{{ $doctor?->name ?: $brand['clinicName'] }}</strong>
                    <small>{{ $doctor?->specialty ?: 'Medicina estética facial y corporal' }}</small>
                </span>
            </a>
        </div>

        <figure class="dk-hero-media">
            @if ($page['doctorPhotoUrl'])
                <img src="{{ $page['doctorPhotoUrl'] }}" alt="Foto de {{ $doctor?->name ?: $brand['clinicName'] }}" class="dk-hero-photo" fetchpriority="high" decoding="async">
            @elseif ($page['promoBanners']->first()['image'] ?? null)
                <img src="{{ $page['promoBanners']->first()['image'] }}" alt="{{ $brand['clinicName'] }}" class="dk-hero-photo" fetchpriority="high" onerror="this.remove()">
            @elseif ($brand['logoUrl'])
                <div class="dk-hero-fallback"><img src="{{ $brand['logoUrl'] }}" alt="Logo de {{ $brand['clinicName'] }}"></div>
            @else
                <div class="dk-hero-fallback" aria-hidden="true"><span>{{ $brand['initials'] }}</span></div>
            @endif
        </figure>
    </div>
</section>
