<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Renombra la marca "Clínica Rejuvenezk" / "Doctor Kevin Rejuvenezk" → "Dr. Kevin Rejuvenezk" en los datos ya guardados.
 * (El nombre del archivo se conserva: es el identificador de la migración.)
 * Solo toca filas cuyo valor sea EXACTAMENTE el nombre anterior (no afecta otras clínicas).
 */
return new class extends Migration
{
    private const OLD_NAMES = ['Clínica Rejuvenezk', 'Clinica Rejuvenezk', 'Doctor Kevin Rejuvenezk'];

    private const NEW_NAME = 'Dr. Kevin Rejuvenezk';

    public function up(): void
    {
        DB::table('clinics')
            ->whereIn('name', self::OLD_NAMES)
            ->update(['name' => self::NEW_NAME, 'updated_at' => now()]);

        $settings = DB::table('site_settings')
            ->where('key', 'clinic_name')
            ->whereIn('value', self::OLD_NAMES)
            ->get(['id', 'clinic_id', 'group']);

        if ($settings->isEmpty()) {
            return;
        }

        DB::table('site_settings')
            ->whereIn('id', $settings->pluck('id'))
            ->update(['value' => self::NEW_NAME, 'updated_at' => now()]);

        // DB::table() no dispara eventos de Eloquent: invalidamos la caché de SiteSetting a mano.
        // Claves posibles según CurrentClinic: id de la clínica, '' (sin clínica) y 'global' (no enlazada).
        foreach ($settings as $setting) {
            foreach (array_unique([(string) $setting->clinic_id, '', 'global']) as $clinicKey) {
                Cache::forget("setting:{$clinicKey}:{$setting->group}:clinic_name");
                Cache::forget("settings_group:{$clinicKey}:{$setting->group}");
            }
        }
    }

    /**
     * No-op intencional: no hay forma segura de distinguir las filas renombradas por esta
     * migración de las que ya se crearon con el nombre nuevo (seeder, Filament).
     * Para revertir, cambia el nombre desde Filament (Ajustes / Clínicas).
     */
    public function down(): void
    {
        //
    }
};
