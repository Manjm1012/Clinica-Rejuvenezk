<?php

namespace App\Support;

/**
 * Interruptor del panel médico (MEDICAL_PANEL_ENABLED, apagado por defecto).
 * Controla /medico, las fórmulas del portal del paciente y los enlaces relacionados.
 */
class MedicalPanel
{
    public static function enabled(): bool
    {
        return (bool) config('clinical.medical_panel_enabled', false);
    }
}
