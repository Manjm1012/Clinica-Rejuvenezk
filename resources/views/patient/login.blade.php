@php
    $clinicName = $settings['clinic_name'] ?? 'Dr. Kevin Rejuvenezk';
    $forgotUrl = \App\Support\PublicSettings::whatsappLink($settings, 'Hola, olvidé mi contraseña del portal de pacientes.');
@endphp

<x-layouts.site :settings="$settings" :title="'Ingresar | ' . $clinicName">
    <main class="dk-portal">
        <section class="dk-portal-card" aria-labelledby="dk-login-title">
            <p class="kicker dk-portal-kicker">Portal de pacientes</p>
            <h1 id="dk-login-title" class="dk-portal-title">Ingresa a tu cuenta</h1>
            <p class="dk-portal-lead">Consulta tu historial de procedimientos y cuándo te toca renovar.</p>

            @if (session('status'))
                <div class="dk-alert dk-alert-info" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('patient.login.attempt') }}" class="dk-portal-form">
                @csrf

                @error('login')
                    <div class="dk-alert dk-alert-error" role="alert" id="login-error">{{ $message }}</div>
                @enderror

                <div class="dk-field">
                    <label for="login">Correo o número de celular</label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required maxlength="200"
                        @error('login') aria-invalid="true" aria-describedby="login-error" @enderror autofocus>
                </div>

                <div class="dk-field">
                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    @error('password')<p id="password-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <label class="dk-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>Recordarme en este dispositivo</span>
                </label>

                <button type="submit" class="dk-btn-primary dk-btn-block">Ingresar</button>
            </form>

            <div class="dk-portal-foot">
                <p>¿Aún no tienes cuenta? <a href="{{ route('patient.register') }}">Crea tu cuenta</a></p>
                <p class="dk-portal-small">
                    ¿Olvidaste tu contraseña?
                    @if ($forgotUrl)
                        <a href="{{ $forgotUrl }}" target="_blank" rel="noopener noreferrer">Escríbenos por WhatsApp</a>
                    @else
                        <a href="{{ route('contact') }}">Contáctanos</a>
                    @endif
                    y te ayudamos a recuperarla.
                </p>
            </div>
        </section>
    </main>
</x-layouts.site>
