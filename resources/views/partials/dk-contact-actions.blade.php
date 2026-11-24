{{--
    Botón flotante de WhatsApp + barra inferior fija (solo móvil).
    Parámetros: $clinicName, $bookingUrl (WhatsApp con mensaje prellenado o /contacto),
    $hasWhatsapp (bool), $phone (string|null).
--}}
@php
    $dkLinkAttrs = $hasWhatsapp ? 'target="_blank" rel="noopener noreferrer"' : '';
    $dkPhoneHref = preg_replace('/[^0-9+]/', '', (string) ($phone ?? ''));
    $dkWaIcon = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91A9.85 9.85 0 0 0 12.04 2Zm0 18.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.25-8.23a8.2 8.2 0 0 1 8.23 8.24c0 4.54-3.7 8.23-8.23 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.78.97-.14.16-.29.18-.54.06-.25-.12-1.04-.38-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.13-.56-1.35-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.22-.16-.47-.28Z"/></svg>';
@endphp

<a href="{{ $bookingUrl }}" class="whatsapp-fab dk-wa-fab" aria-label="{{ $hasWhatsapp ? 'Escribir por WhatsApp a ' . $clinicName : 'Reservar valoración' }}" {!! $dkLinkAttrs !!}>
    <span class="dk-wa-fab-icon">{!! $dkWaIcon !!}</span>
    <strong>WhatsApp</strong>
</a>

<nav class="dk-mobilebar" aria-label="Contacto rápido">
    @if ($dkPhoneHref !== '')
        <a href="tel:{{ $dkPhoneHref }}" class="dk-mobilebar-btn dk-mobilebar-call">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
            <span>Llamar</span>
        </a>
    @endif
    <a href="{{ $bookingUrl }}" class="dk-mobilebar-btn dk-mobilebar-wa" {!! $dkLinkAttrs !!}>
        @if ($hasWhatsapp)
            {!! $dkWaIcon !!}
            <span>WhatsApp</span>
        @else
            <span>Reservar valoración</span>
        @endif
    </a>
</nav>
