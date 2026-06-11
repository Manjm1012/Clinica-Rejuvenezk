<?php

namespace App\Filament\Pages\Auth;

use App\Models\Clinic;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        return 'Acceso administrativo | ' . $this->getClinicName();
    }

    public function getHeading(): string | Htmlable
    {
        return 'Bienvenido a ' . $this->getClinicName();
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Ingresa al panel editorial para gestionar contenidos, tratamientos y solicitudes con una experiencia clara y ordenada.';
    }

    protected function getClinicName(): string
    {
        return Clinic::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->value('name') ?? 'Clínica Rejuvenezk';
    }
}