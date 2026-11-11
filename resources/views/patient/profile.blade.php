@php
    $fmtDate = fn ($date) => $date->copy()->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $fieldError = fn (string $field) => $errors->has($field) ? 'aria-invalid="true" aria-describedby="' . $field . '-error"' : '';
@endphp

<x-layouts.site :settings="$settings" :title="'Mi perfil | ' . $clinicName">
    <main class="dk-portal">
        <section class="dk-portal-card" aria-labelledby="dk-profile-title">
            <p class="dk-portal-back"><a href="{{ route('patient.dashboard') }}">← Volver a mi cuenta</a></p>
            <h1 id="dk-profile-title" class="dk-portal-title">Mi perfil</h1>

            @if (session('status'))
                <div class="dk-alert dk-alert-info" role="status">{{ session('status') }}</div>
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

            <form method="POST" action="{{ route('patient.profile.update') }}" class="dk-portal-form">
                @csrf
                @method('PUT')

                <div class="dk-field">
                    <label for="name">Nombre completo</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $patient->name) }}" autocomplete="name" required maxlength="200" {!! $fieldError('name') !!}>
                    @error('name')<p id="name-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" value="{{ $patient->email }}" disabled aria-describedby="email-help">
                    <p id="email-help" class="dk-field-help">Para cambiar tu correo escríbenos.</p>
                </div>

                <div class="dk-field">
                    <label for="phone">Celular (WhatsApp)</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $patient->phone) }}" autocomplete="tel" inputmode="tel" required maxlength="30" {!! $fieldError('phone') !!}>
                    @error('phone')<p id="phone-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <label class="dk-check">
                    <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent', $patient->marketing_consent))>
                    <span>Quiero recibir recordatorios y promociones por WhatsApp o correo.</span>
                </label>
                @if ($patient->marketing_consent_at)
                    <p class="dk-field-help">Última actualización de esta preferencia: {{ $fmtDate($patient->marketing_consent_at) }}.</p>
                @endif

                <fieldset class="dk-fieldset">
                    <legend>Cambiar contraseña <span>(opcional)</span></legend>

                    <div class="dk-field">
                        <label for="current_password">Contraseña actual</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" {!! $fieldError('current_password') !!}>
                        @error('current_password')<p id="current_password-error" class="dk-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="dk-field">
                        <label for="password">Nueva contraseña</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" {!! $fieldError('password') !!}>
                        @error('password')<p id="password-error" class="dk-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="dk-field">
                        <label for="password_confirmation">Confirma la nueva contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8">
                    </div>
                </fieldset>

                <button type="submit" class="dk-btn-primary dk-btn-block">Guardar cambios</button>
            </form>
        </section>
    </main>
</x-layouts.site>
