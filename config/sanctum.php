<?php

// Solo se sobrescriben estas claves; el resto usa los valores por defecto del paquete.
return [
    // Minutos de vigencia de los tokens de la API (null = nunca vencen).
    'expiration' => env('SANCTUM_EXPIRATION', 60 * 24 * 30),
];
