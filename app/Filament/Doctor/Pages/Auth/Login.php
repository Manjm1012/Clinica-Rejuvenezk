<?php

namespace App\Filament\Doctor\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Acceso médico';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Panel médico';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Consulta la historia de tus pacientes, registra consultas y emite fórmulas con indicaciones.';
    }
}
