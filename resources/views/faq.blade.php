@php
    $brand = \App\Support\SiteBrand::for($settings);

    // Datos estructurados FAQPage (resultados enriquecidos en Google).
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $groups->flatten(1)->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $faq->answer)],
        ])->values()->all(),
    ];
@endphp

<x-layouts.site
    :settings="$settings"
    :title="'Preguntas frecuentes | ' . $brand['clinicName']"
    :description="'Resolvemos las dudas más comunes sobre nuestros tratamientos, la valoración y los cuidados posteriores.'"
>
    @if ($groups->isNotEmpty())
        @push('head')
            <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
        @endpush
    @endif

    <main>
        <x-page-header kicker="Preguntas frecuentes" title="Resolvemos tus dudas">
            ¿No encuentras tu pregunta? <a href="{{ $brand['bookingUrl'] }}" @if($brand['hasWhatsapp']) target="_blank" rel="noopener noreferrer" @endif>Escríbenos</a> y te respondemos.
        </x-page-header>

        <section class="container dk-faq-list">
            @forelse ($groups as $group => $faqs)
                <h2 class="dk-faq-group-title">{{ $group }}</h2>
                @foreach ($faqs as $faq)
                    <details class="dk-faq-item">
                        <summary>{{ $faq->question }}</summary>
                        <div>{{ $faq->answer }}</div>
                    </details>
                @endforeach
            @empty
                <p>Pronto publicaremos aquí las preguntas más frecuentes.</p>
            @endforelse
        </section>
    </main>
</x-layouts.site>
