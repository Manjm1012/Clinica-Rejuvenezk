{{-- Enlaces principales (escritorio y móvil). En la portada, Servicios es un ancla con scrollspy.
     $withAccount = false: "Ingresar"/"Mi cuenta" va aparte (en escritorio, junto al CTA de la barra). --}}
<a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}" data-nav-external="about">Quiénes somos</a>
@if ($onHome)
    <a href="#servicios" data-nav-section="servicios">Servicios</a>
@else
    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-active' : '' }}">Servicios</a>
@endif
<a href="{{ route('results') }}" class="{{ request()->routeIs('results') ? 'is-active' : '' }}">Resultados</a>
<a href="{{ route('team') }}" class="{{ request()->routeIs('team') ? 'is-active' : '' }}">Equipo médico</a>
<a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contacto</a>
@if ($withAccount ?? true)
    @include('partials.dk-patient-nav-link')
@endif
