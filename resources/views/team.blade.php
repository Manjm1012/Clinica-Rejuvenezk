@php
    $brand = \App\Support\SiteBrand::for($settings);

    // Datos estructurados: cada médico como Physician.
    $physicians = $doctors->map(fn ($doctor) => array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Physician',
        'name' => $doctor->name,
        'description' => $doctor->specialty,
        'image' => \App\Support\Media::url($doctor->photo_path),
        'worksFor' => ['@type' => 'MedicalClinic', 'name' => $brand['clinicName']],
    ]))->values();
@endphp

<x-layouts.site
    :settings="$settings"
    :title="'Equipo médico | ' . $brand['clinicName']"
    :description="'Conoce a los médicos de ' . $brand['clinicName'] . ': formación, certificaciones y especialidad.'"
>
    @push('head')
        <script type="application/ld+json">{!! json_encode($physicians, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endpush

    <main>
        <x-page-header kicker="Equipo médico" title="Médicos certificados, criterio y cercanía">
            Cada valoración la realiza un médico. Conoce su formación antes de tu cita.
        </x-page-header>

        <section class="container dk-team-grid" aria-label="Médicos de {{ $brand['clinicName'] }}">
            @forelse ($doctors as $doctor)
                @php($photo = \App\Support\Media::url($doctor->photo_path))
                <article class="dk-team-card">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="Foto de {{ $doctor->name }}" class="dk-team-photo" loading="lazy" decoding="async">
                    @else
                        <div class="dk-team-photo dk-team-photo--empty" aria-hidden="true">{{ \App\Support\Media::initials($doctor->name) }}</div>
                    @endif
                    <div class="dk-team-body">
                        <h2>{{ $doctor->name }}</h2>
                        @if ($doctor->specialty)<p class="dk-team-specialty">{{ $doctor->specialty }}</p>@endif
                        @if ($doctor->subtitle)<p class="dk-team-bio"><em>{{ $doctor->subtitle }}</em></p>@endif
                        @if ($doctor->bio)<p class="dk-team-bio">{{ \Illuminate\Support\Str::limit($doctor->bio, 420) }}</p>@endif

                        @if ($doctor->credentials->isNotEmpty() || $doctor->university)
                            <ul class="dk-team-credentials">
                                @if ($doctor->university)<li>{{ $doctor->university }}</li>@endif
                                @foreach ($doctor->credentials as $credential)
                                    <li>{{ $credential->title }}{{ $credential->institution ? ' · '.$credential->institution : '' }}{{ $credential->year ? ' ('.$credential->year.')' : '' }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($doctor->professional_registration)
                            <p class="dk-field-help">Registro profesional: {{ $doctor->professional_registration }}</p>
                        @endif
                        @if ($doctor->instagram_url)
                            <a href="{{ $doctor->instagram_url }}" class="svc-card-link" target="_blank" rel="noopener noreferrer">Instagram →</a>
                        @endif
                    </div>
                </article>
            @empty
                <p>Pronto presentaremos aquí a nuestro equipo.</p>
            @endforelse
        </section>
    </main>
</x-layouts.site>
