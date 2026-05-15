<?php

return [
    'temporary_file_upload' => [
        'disk' => 'public',
        'directory' => 'livewire-tmp',
        'middleware' => 'throttle:240,1',
        'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        'max_upload_time' => 15,
    ],
];