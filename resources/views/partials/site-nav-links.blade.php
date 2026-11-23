{{-- Enlaces principales (escritorio y móvil). En la portada, Servicios y Contacto son anclas con scrollspy. --}}
<a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}" data-nav-external="about">Quiénes somos</a>
@if ($onHome)
    <a href="#servicios" data-nav-section="servicios">Servicios</a>
@else
    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-active' : '' }}">Servicios</a>
@endif
<a href="{{ route('results') }}" class="{{ request()->routeIs('results') ? 'is-active' : '' }}">Resultados</a>
<a href="{{ route('team') }}" class="{{ request()->routeIs('team') ? 'is-active' : '' }}">Equipo médico</a>
<a href="{{ $onHome ? '#contacto' : route('home').'#contacto' }}" @if($onHome) data-nav-section="contacto" @endif>Contacto</a>
@include('partials.dk-patient-nav-link')
