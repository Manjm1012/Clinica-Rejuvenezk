{{-- Contacto y agenda: formulario de leads (CreateLead + StoreLeadRequest) y canales de los ajustes públicos. --}}
@php
    $brand = \App\Support\SiteBrand::for($settings);
    $phoneHref = preg_replace('/[^\d+]/', '', (string) $brand['phone']);
    $hours = trim((string) ($settings['business_hours'] ?? ''));
    $social = collect($brand['social'])->whereIn('icon', ['instagram', 'facebook', 'tiktok']);
    $selected = (string) old('service_id', $selectedServiceId);
@endphp

<x-layouts.site
    :settings="$settings"
    :title="'Contacto y citas | ' . $brand['clinicName']"
    :description="'Agenda tu valoración o pide información en ' . $brand['clinicName'] . '. Déjanos tus datos y te contactamos.'"
>
    <main>
        <x-page-header kicker="Contacto" title="Agenda tu valoración">
            Déjanos tus datos y te contactamos para resolver tus dudas y agendar tu cita. Si lo prefieres, escríbenos por el canal que más uses.
        </x-page-header>

        <section class="container dk-page-body dk-contact-grid" aria-label="Formulario y canales de contacto">
            <form id="formulario" method="POST" action="{{ route('leads.store') }}" class="contact-form landing-contact-form dk-lead-form" @if($errors->any()) data-scroll-into-view @endif>
                @csrf
                <input type="hidden" name="source" value="website">
                @foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $utm)
                    @if (is_string(request($utm)) && request()->filled($utm))
                        <input type="hidden" name="{{ $utm }}" value="{{ request($utm) }}">
                    @endif
                @endforeach

                <h2 class="dk-form-title">Solicita tu valoración o información</h2>

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
                    <label for="lead-email">Correo <span class="dk-field-optional">(opcional)</span></label>
                    <input id="lead-email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" maxlength="200"
                           @error('email') aria-invalid="true" aria-describedby="lead-email-error" @enderror>
                    @error('email')<p id="lead-email-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="lead-service">Tratamiento de interés</label>
                    <select id="lead-service" name="service_id" @error('service_id') aria-invalid="true" aria-describedby="lead-service-error" @enderror>
                        <option value="">Aún no lo sé / quiero asesoría</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected($selected === (string) $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                    @error('service_id')<p id="lead-service-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="dk-field">
                    <label for="lead-message">Mensaje <span class="dk-field-optional">(opcional)</span></label>
                    <textarea id="lead-message" name="message" rows="3" maxlength="2000"
                              @error('message') aria-invalid="true" aria-describedby="lead-message-error" @enderror>{{ old('message') }}</textarea>
                    @error('message')<p id="lead-message-error" class="dk-field-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="dk-btn-primary dk-btn-block">Solicitar valoración</button>

                <p class="dk-form-legal">Al enviar aceptas nuestra <a href="{{ route('legal.data-policy') }}">política de tratamiento de datos</a>.</p>
            </form>

            <aside class="dk-contact-channels" aria-labelledby="dk-contact-channels-title">
                <h2 id="dk-contact-channels-title" class="dk-form-title">Otros canales</h2>

                @if ($brand['hasWhatsapp'] || $phoneHref !== '' || $brand['email'] || $social->isNotEmpty())
                    <ul class="dk-contact-list">
                        @if ($brand['hasWhatsapp'])
                            <li><a href="{{ $brand['bookingUrl'] }}" class="dk-contact-btn dk-contact-btn--wa" target="_blank" rel="noopener noreferrer"><x-site-icon name="whatsapp" /><span>WhatsApp</span></a></li>
                        @endif
                        @if ($phoneHref !== '')
                            <li><a href="tel:{{ $phoneHref }}" class="dk-contact-btn"><x-site-icon name="phone" /><span>Llamar · {{ $brand['phone'] }}</span></a></li>
                        @endif
                        @if ($brand['email'])
                            <li><a href="mailto:{{ $brand['email'] }}" class="dk-contact-btn"><x-site-icon name="mail" /><span>{{ $brand['email'] }}</span></a></li>
                        @endif
                        @foreach ($social as $item)
                            <li><a href="{{ $item['url'] }}" class="dk-contact-btn" target="_blank" rel="noopener noreferrer"><x-site-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span></a></li>
                        @endforeach
                    </ul>
                @endif

                @if ($brand['address'] || $hours !== '')
                    <dl class="dk-contact-info">
                        @if ($brand['address'])
                            <div><dt>Dirección</dt><dd>{{ $brand['address'] }}</dd></div>
                        @endif
                        @if ($hours !== '')
                            <div><dt>Horarios</dt><dd>{!! nl2br(e($hours)) !!}</dd></div>
                        @endif
                    </dl>
                @endif
            </aside>
        </section>
    </main>
</x-layouts.site>
