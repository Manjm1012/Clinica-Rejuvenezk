<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Protege los rollbacks: un down() que elimine tablas o columnas con datos de pacientes
 * se niega en lugar de borrarlos. Para revertir en producción se revierte el CÓDIGO, no las migraciones.
 */
class MigrationGuard
{
    /**
     * @param  array<string, Closure|null>  $tables  tabla => filtro opcional (qué filas cuentan como datos)
     */
    public static function refuseIfHasData(string $migration, array $tables): void
    {
        foreach ($tables as $table => $filter) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table);

            if ($filter) {
                $filter($query);
            }

            if ($query->exists()) {
                throw new RuntimeException(
                    "Rollback de {$migration} cancelado: la tabla {$table} tiene datos de pacientes y se perderían. "
                    .'Para volver atrás, despliega la versión anterior del código sin hacer rollback (ver DEPLOYMENT.md).'
                );
            }
        }
    }
}
