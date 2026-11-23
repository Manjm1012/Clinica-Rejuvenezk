@php
    $brand = \App\Support\SiteBrand::for($settings);
    $hasContent = $beforeAfter->isNotEmpty() || $gallery->isNotEmpty() || $videos->isNotEmpty() || $testimonials->isNotEmpty();
@endphp

<x-layouts.site
    :settings="$settings"
    :title="'Resultados reales | ' . $brand['clinicName']"
    :description="'Antes y después, videos y opiniones de pacientes de ' . $brand['clinicName'] . '. Los resultados pueden variar según cada paciente.'"
    :image="$beforeAfter->first()['after'] ?? $gallery->first()['url'] ?? null"
>
    <main>
        <header class="container dk-page-head">
            <p class="kicker">Resultados</p>
            <h1 class="section-title">Cambios reales, resultados naturales</h1>
            <p class="section-intro">Casos de pacientes que autorizaron compartir sus resultados. Cada rostro es distinto: los resultados pueden variar según cada paciente.</p>
        </header>

        @include('home.before-after', ['pairs' => $beforeAfter])
        @include('home.results', ['testimonialsLimit' => 12])

        @unless ($hasContent)
            <section class="container dk-page-head">
                <p>Pronto publicaremos aquí casos y opiniones de nuestros pacientes.</p>
                <a href="{{ route('home') }}#formulario" class="dk-btn-primary">Reservar valoración</a>
            </section>
        @endunless
    </main>
</x-layouts.site>
