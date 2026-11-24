<?php

/**
 * Crea las carpetas de escritura que Laravel necesita (storage y bootstrap/cache).
 *
 * Es un archivo y no un `php -r "..."` en composer.json porque en Linux el shell
 * expandía las variables `$dir` dentro de las comillas dobles y el comando fallaba.
 * Funciona igual en Linux (Dokploy/Nixpacks) y en Windows (XAMPP).
 *
 * Uso: php scripts/deploy/ensure-storage.php
 */
$root = dirname(__DIR__, 2);

$directories = [
    'bootstrap/cache',
    'storage/app/public',
    'storage/app/private',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

$failed = false;

foreach ($directories as $relative) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

    if (! is_dir($path) && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
        fwrite(STDERR, "No se pudo crear {$relative}".PHP_EOL);
        $failed = true;

        continue;
    }

    // En volúmenes montados el chmod puede no estar permitido: no es un error.
    @chmod($path, 0775);

    if (! is_writable($path)) {
        fwrite(STDERR, "Sin permiso de escritura en {$relative}".PHP_EOL);
        $failed = true;
    }
}

echo $failed ? 'Carpetas de storage: con errores'.PHP_EOL : 'Carpetas de storage: listas'.PHP_EOL;

exit($failed ? 1 : 0);
