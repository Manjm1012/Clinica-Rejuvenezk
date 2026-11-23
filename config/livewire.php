<?php

return [
    'temporary_file_upload' => [
        'disk' => 'local', // privado: los archivos sin validar no deben quedar publicos
        'directory' => 'livewire-tmp',
        'middleware' => 'throttle:240,1',
        'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,mov', 'max:512000'],
        'max_upload_time' => 600,
    ],
];
