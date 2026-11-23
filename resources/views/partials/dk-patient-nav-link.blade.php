{{-- Enlace del portal del paciente en el menú: "Mi cuenta" con sesión, "Ingresar" sin ella.
     No muestra nada mientras las rutas o el guard "patient" no existan. --}}
@php
    $dkPatientLoggedIn = config('auth.guards.patient') !== null && auth('patient')->check();
    $dkPatientLink = null;

    if ($dkPatientLoggedIn && \Illuminate\Support\Facades\Route::has('patient.dashboard')) {
        $dkPatientLink = ['url' => route('patient.dashboard'), 'label' => 'Mi cuenta', 'active' => request()->routeIs('patient.dashboard', 'patient.profile*')];
    } elseif (! $dkPatientLoggedIn && \Illuminate\Support\Facades\Route::has('patient.login')) {
        $dkPatientLink = ['url' => route('patient.login'), 'label' => 'Ingresar', 'active' => request()->routeIs('patient.login', 'patient.register')];
    }
@endphp
@if ($dkPatientLink)
    <a href="{{ $dkPatientLink['url'] }}" class="dk-nav-account {{ $dkPatientLink['active'] ? 'is-active' : '' }}" @if($dkPatientLink['active']) aria-current="page" @endif>{{ $dkPatientLink['label'] }}</a>
@endif
