<?php

/**
 * Ejecuta `php artisan storage:link` solo si public/storage no existe.
 *
 * Reemplaza el `php -r "... $exitCode ..."` de composer.json, que en Linux fallaba
 * porque el shell expandía `$exitCode` dentro de las comillas dobles.
 *
 * Uso: php scripts/deploy/storage-link.php
 */
$root = dirname(__DIR__, 2);
$link = $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'storage';

// is_link() cubre enlaces simbólicos (Linux) y file_exists() las uniones de carpetas (Windows).
if (is_link($link) || file_exists($link)) {
    echo 'public/storage ya existe'.PHP_EOL;
    exit(0);
}

$command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.DIRECTORY_SEPARATOR.'artisan').' storage:link';
passthru($command, $exitCode);

exit($exitCode);
