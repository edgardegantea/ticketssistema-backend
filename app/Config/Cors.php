<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 */
class Cors extends BaseConfig
{
    // Config por defecto que usará el filtro si no se especifica otra
    public array $default = [
        'allowedOrigins'         => ['http://localhost:5173'],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => false,
        'allowedHeaders'         => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'                 => 3600,
    ];

    // (Opcional) perfil api, si quisieras otro distinto
    public array $api = [
        'allowedOrigins'         => ['http://localhost:5173'],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => false,
        'allowedHeaders'         => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'                 => 3600,
    ];
}
