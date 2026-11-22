<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    // DoctorPanelProvider se registra en AppServiceProvider solo si MEDICAL_PANEL_ENABLED=true.
];
