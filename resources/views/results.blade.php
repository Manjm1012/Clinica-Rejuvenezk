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
        <x-page-header kicker="Resultados" title="Cambios reales, resultados naturales">
            Casos de pacientes que autorizaron compartir sus resultados. Cada rostro es distinto: los resultados pueden variar según cada paciente.
        </x-page-header>

        {{-- El encabezado ya dice "Resultados": las secciones van sin kicker y con su propio subtítulo. --}}
        @include('home.before-after', ['pairs' => $beforeAfter, 'showKicker' => false, 'sectionTitle' => 'Antes y después'])
        @include('home.results', ['testimonialsLimit' => 12, 'showKicker' => false, 'sectionTitle' => 'Videos, galería y opiniones'])

        @unless ($hasContent)
            <section class="container dk-page-body">
                <p>Pronto publicaremos aquí casos y opiniones de nuestros pacientes.</p>
                <a href="{{ route('home') }}#formulario" class="dk-btn-primary">Reservar valoración</a>
            </section>
        @endunless
    </main>
</x-layouts.site>
