@php
    $clinicName = $settings['clinic_name'] ?? 'Dr. Kevin Rejuvenezk';
    $fieldError = fn (string $field) => $errors->has($field) ? 'aria-invalid="true" aria-describedby="' . $field . '-error"' : '';
@endphp

<x-layouts.site :settings="$settings" :title="'Crear cuenta | ' . $clinicName">
    <main class="dk-portal">
        <section class="dk-portal-card" aria-labelledby="dk-register-title">
            <p class="kicker dk-portal-kicker">Portal de pacientes</p>
            <h1 id="dk-register-title" class="dk-portal-title">Crea tu cuenta</h1>
            <p class="dk-portal-lead">Guarda tu historial de procedimientos y recibe a tiempo tus renovaciones.</p>

            @if (session('patient_exists'))
                <div class="dk-alert dk-alert-warn" role="alert">
                    <p>Ya tenemos un registro con estos datos. Escríbenos por WhatsApp para activar tu cuenta.</p>
                    <a href="{{ $activationWhatsappUrl ?? route('home') . '#formulario' }}" class="dk-alert-action"
                        @if($activationWhatsappUrl) target="_blank" rel="noopener noreferrer" @endif>
                        {{ $activationWhatsappUrl ? 'Escribir por WhatsApp' : 'Contactar a la clínica' }}
                    </a>
                </div>
            @endif

            @if ($errors->any())
                <div class="dk-alert dk-alert-error" role="alert">
                    <p>Revisa los datos marcados:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('patient.register.store') }}" class="dk-portal-form">
                @csrf

                <div class="dk-field">
                    <label for="name">Nombre completo</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="200" {!! $fieldError('name') !!}>
                    @error('name')<p id="name-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="200" {!! $fieldError('email') !!}>
                    @error('email')<p id="email-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="phone">Celular (WhatsApp)</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" required maxlength="30" {!! $fieldError('phone') !!}>
                    @error('phone')<p id="phone-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required minlength="8" aria-describedby="password-help{{ $errors->has('password') ? ' password-error' : '' }}" @error('password') aria-invalid="true" @enderror>
                    <p id="password-help" class="dk-field-help">Mínimo 8 caracteres.</p>
                    @error('password')<p id="password-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="password_confirmation">Confirma tu contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
                </div>

                <label class="dk-check">
                    <input type="checkbox" name="data_policy" value="1" required @checked(old('data_policy')) {!! $fieldError('data_policy') !!}>
                    <span>Acepto la <a href="{{ route('legal.data-policy') }}" target="_blank" rel="noopener">política de tratamiento de datos personales</a> (obligatorio).</span>
                </label>
                @error('data_policy')<p id="data_policy-error" class="dk-field-error">{{ $message }}</p>@enderror

                <label class="dk-check">
                    <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent'))>
                    <span>Quiero recibir recordatorios y promociones por WhatsApp o correo (opcional).</span>
                </label>

                <button type="submit" class="dk-btn-primary dk-btn-block">Crear mi cuenta</button>
            </form>

            <div class="dk-portal-foot">
                <p>¿Ya tienes cuenta? <a href="{{ route('patient.login') }}">Ingresa aquí</a></p>
            </div>
        </section>
    </main>
</x-layouts.site>
