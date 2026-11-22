@php($texts = $page['texts'])
<section id="contacto" class="section section-white cta-wrap">
    <div class="container contact-layout reveal reveal-2">
        <div class="cta">
            <div>
                <p class="kicker">{{ $texts['ctaKicker'] }}</p>
                <h2>{{ $texts['ctaTitle'] }}</h2>
                <p>{{ $brand['address'] ?: $texts['ctaBody'] }}</p>
                <p class="cta-note">{{ $texts['ctaNote'] }}</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="{{ $brand['bookingUrl'] }}" @if($brand['hasWhatsapp']) target="_blank" rel="noopener noreferrer" @endif>{{ $texts['ctaWhatsappLabel'] }}</a>
                <a class="btn btn-ghost" href="{{ $brand['email'] ? 'mailto:'.$brand['email'] : '#formulario' }}">{{ $brand['email'] ?: $texts['ctaEmailLabel'] }}</a>
            </div>
        </div>

        <form id="formulario" method="POST" action="{{ route('leads.store') }}" class="contact-form landing-contact-form dk-lead-form reveal reveal-3" @if($errors->any()) data-scroll-into-view @endif>
            @csrf
            <input type="hidden" name="source" value="website">
            @foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $utm)
                @if (request()->filled($utm))
                    <input type="hidden" name="{{ $utm }}" value="{{ request($utm) }}">
                @endif
            @endforeach

            <p class="dk-form-title">Solicita tu valoración</p>

            @if ($errors->any())
                <div class="dk-form-errors" role="alert">
                    <p>Revisa los datos marcados:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dk-field">
                <label for="lead-name">Nombre</label>
                <input id="lead-name" type="text" name="name" autocomplete="name" value="{{ old('name') }}" required maxlength="200"
                       @error('name') aria-invalid="true" aria-describedby="lead-name-error" @enderror>
                @error('name')<p id="lead-name-error" class="dk-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="dk-field">
                <label for="lead-phone">WhatsApp o teléfono</label>
                <input id="lead-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" required maxlength="30"
                       @error('phone') aria-invalid="true" aria-describedby="lead-phone-error" @enderror>
                @error('phone')<p id="lead-phone-error" class="dk-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="dk-field">
                <label for="lead-service">Tratamiento de interés</label>
                <select id="lead-service" name="service_id">
                    <option value="">Aún no lo sé / quiero asesoría</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="dk-btn-primary dk-btn-block">Solicitar valoración</button>

            <p class="dk-form-legal">Al enviar aceptas nuestra <a href="{{ route('legal.data-policy') }}">política de tratamiento de datos</a>.</p>
        </form>
    </div>
</section>
