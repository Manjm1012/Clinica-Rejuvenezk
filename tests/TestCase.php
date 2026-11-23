<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Estado del panel médico (MEDICAL_PANEL_ENABLED) para la clase de prueba.
     * Por defecto apagado, igual que en producción; las pruebas del panel lo encienden.
     */
    protected bool $medicalPanel = false;

    public function createApplication()
    {
        // Se fija antes de arrancar la app: el proveedor del panel y las rutas se registran al iniciar.
        $value = $this->medicalPanel ? 'true' : 'false';
        putenv("MEDICAL_PANEL_ENABLED={$value}");
        $_ENV['MEDICAL_PANEL_ENABLED'] = $value;
        $_SERVER['MEDICAL_PANEL_ENABLED'] = $value;

        return parent::createApplication();
    }
}
