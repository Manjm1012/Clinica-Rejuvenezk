{{-- Portada. Los datos derivados vienen de App\View\HomePage; cada sección es un parcial en views/home/. --}}
@php
    $brand = \App\Support\SiteBrand::for($settings);
@endphp

<x-layouts.site
    :settings="$settings"
    :title="$brand['clinicName'] . ' | Medicina estética con resultados naturales'"
    :description="$page['hero']['subtitle']"
    body-class="landing-body"
>
    <main id="inicio">
        @include('home.hero')
        @include('home.trust')
        @include('home.services')
        @include('home.before-after', ['pairs' => $page['beforeAfter']])
        @include('home.results')
        @include('home.promos')
        @include('home.contact')
    </main>
</x-layouts.site>
