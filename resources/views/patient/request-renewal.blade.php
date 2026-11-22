@php
    $fmtDate = fn ($date) => $date->copy()->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $serviceName = $treatment->service?->name ?? 'tratamiento';
    $fieldError = fn (string $field) => $errors->has($field) ? 'aria-invalid="true" aria-describedby="' . $field . '-error"' : '';
    $suggested = $treatment->due_date && $treatment->due_date->isFuture() ? $treatment->due_date->toDateString() : now()->addDays(2)->toDateString();
@endphp

<x-layouts.site :settings="$settings" :title="'Renovar ' . $serviceName . ' | ' . $clinicName">
    <main class="dk-portal">
        <section class="dk-portal-card" aria-labelledby="dk-renew-title">
            <p class="dk-portal-back"><a href="{{ route('patient.dashboard') }}">← Volver a mi cuenta</a></p>
            <p class="kicker dk-portal-kicker">Solicitar renovación</p>
            <h1 id="dk-renew-title" class="dk-portal-title">{{ $serviceName }}</h1>
            <p class="dk-field-help">
                Tu último procedimiento fue el {{ $fmtDate($treatment->performed_at) }}
                @if ($treatment->doctor) con {{ $treatment->doctor->name }}@endif.
                @if ($treatment->due_date) La renovación sugerida es el {{ $fmtDate($treatment->due_date) }}.@endif
            </p>

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

            <form method="POST" action="{{ route('patient.requests.store', $treatment->id) }}" class="dk-portal-form">
                @csrf

                <div class="dk-field">
                    <label for="preferred_date">¿Qué día te gustaría venir?</label>
                    <input id="preferred_date" type="date" name="preferred_date"
                           value="{{ old('preferred_date', $suggested) }}"
                           min="{{ now()->addDay()->toDateString() }}" max="{{ now()->addDays(120)->toDateString() }}"
                           required {!! $fieldError('preferred_date') !!}>
                    @error('preferred_date')<p id="preferred_date-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <fieldset class="dk-fieldset">
                    <legend>¿En qué horario?</legend>
                    @foreach ($times as $value => $label)
                        <label class="dk-check">
                            <input type="radio" name="preferred_time" value="{{ $value }}" @checked(old('preferred_time', 'manana') === $value) required>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    @error('preferred_time')<p class="dk-field-error">{{ $message }}</p>@enderror
                </fieldset>

                <div class="dk-field">
                    <label for="message">¿Algo que debamos saber? <span class="dk-field-help">(opcional)</span></label>
                    <textarea id="message" name="message" rows="3" maxlength="1000" {!! $fieldError('message') !!}>{{ old('message') }}</textarea>
                    @error('message')<p id="message-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <p class="dk-field-help">Es una preferencia: te confirmaremos la fecha y hora exactas por WhatsApp.</p>

                <button type="submit" class="dk-btn-primary">Enviar solicitud</button>
            </form>
        </section>
    </main>
</x-layouts.site>
