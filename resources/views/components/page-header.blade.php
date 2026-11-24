{{--
    Encabezado común de las páginas internas (Servicios, Resultados, Equipo médico, FAQ...).
    Mismo contenedor y tipografía en todas, y el único <h1> de la página.
    Uso: <x-page-header kicker="Resultados" title="Cambios reales">Texto de introducción</x-page-header>
--}}
@props(['kicker' => null, 'title'])

<header {{ $attributes->merge(['class' => 'container dk-page-head']) }}>
    @if ($kicker)
        <p class="kicker">{{ $kicker }}</p>
    @endif
    <h1 class="section-title">{{ $title }}</h1>
    @if ($slot->isNotEmpty())
        <p class="section-intro">{{ $slot }}</p>
    @endif
</header>
