@props(['name'])
{{-- Iconos SVG del sitio público. Uso: <x-site-icon name="whatsapp" /> --}}
@switch($name)
    @case('instagram')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.9A3.85 3.85 0 0 0 3.9 7.75v8.5a3.85 3.85 0 0 0 3.85 3.85h8.5a3.85 3.85 0 0 0 3.85-3.85v-8.5a3.85 3.85 0 0 0-3.85-3.85h-8.5Zm8.9 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9Z"/></svg>
        @break
    @case('facebook')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.5 1.7-1.5h1.6V5c-.8-.1-1.7-.2-2.6-.2-2.6 0-4.2 1.6-4.2 4.4V11H8v3h2.5v8h3Z"/></svg>
        @break
    @case('tiktok')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M14.9 3c.2 1.8 1.2 3.4 2.8 4.3 1 .6 2.1.9 3.3.9v3.1c-1.4 0-2.7-.3-3.9-.9-.8-.4-1.5-.9-2.2-1.5v6.1c0 3.4-2.7 6-6.1 6a6 6 0 0 1-6-6c0-3.4 2.7-6.1 6-6.1.3 0 .6 0 .9.1v3.1a3 3 0 1 0 2.2 2.9V3h3Z"/></svg>
        @break
    @case('youtube')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M22 12s0-3-.4-4.4a3.1 3.1 0 0 0-2.2-2.2C18 5 12 5 12 5s-6 0-7.4.4a3.1 3.1 0 0 0-2.2 2.2C2 9 2 12 2 12s0 3 .4 4.4a3.1 3.1 0 0 0 2.2 2.2C6 19 12 19 12 19s6 0 7.4-.4a3.1 3.1 0 0 0 2.2-2.2C22 15 22 12 22 12Zm-12.3 3.5V8.5l5.8 3.5-5.8 3.5Z"/></svg>
        @break
    @case('whatsapp')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M12 2.2a9.8 9.8 0 0 0-8.4 14.9L2 22l5-1.6A9.8 9.8 0 1 0 12 2.2Zm0 17.8c-1.5 0-3-.4-4.2-1.2l-.3-.2-2.9.9.9-2.8-.2-.3A7.8 7.8 0 1 1 12 20Zm4.3-5.9c-.2-.1-1.3-.6-1.5-.7s-.4-.1-.6.1-.7.7-.8.8-.3.2-.5.1a6.4 6.4 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.4.1-.5l.4-.4c.1-.1.2-.2.2-.4l.1-.3c0-.1-.6-1.6-.8-2.1-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.3c.1.2 1.6 2.5 3.9 3.5.5.2 1 .4 1.4.5.6.2 1.1.2 1.5.1.5-.1 1.3-.5 1.5-1 .2-.5.2-.9.1-1 0-.1-.2-.2-.4-.3Z"/></svg>
        @break
    @case('mail')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M3 5.5h18A1.5 1.5 0 0 1 22.5 7v10A1.5 1.5 0 0 1 21 18.5H3A1.5 1.5 0 0 1 1.5 17V7A1.5 1.5 0 0 1 3 5.5Zm0 1.7a.3.3 0 0 0-.3.3v.2l9.3 6.3 9.3-6.3v-.2a.3.3 0 0 0-.3-.3H3Zm18 9.6a.3.3 0 0 0 .3-.3V9.8l-8.8 6a.9.9 0 0 1-1 0l-8.8-6v6.7c0 .2.1.3.3.3H21Z"/></svg>
        @break
    @case('phone')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
        @break
    @case('shield')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Zm-1.2 13.6-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4-6.3 6.3Z"/></svg>
        @break
    @case('doctor')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5Zm4.5 2.5h-1.5V15h-1.5v1.5H12V18h1.5v1.5H15V18h1.5v-1.5Z"/></svg>
        @break
    @case('spark')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M12 2l1.9 5.6L19.5 9.5l-5.6 1.9L12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2Zm6.5 11 .9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9.9-2.6Z"/></svg>
        @break
    @default
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {{ $attributes }}><path d="M12 21s-7.5-4.6-9.4-9.3C1.3 8.4 3.3 5 6.7 5c2 0 3.3 1 4.3 2.4h2C14 6 15.3 5 17.3 5c3.4 0 5.4 3.4 4.1 6.7C19.5 16.4 12 21 12 21Z"/></svg>
@endswitch
