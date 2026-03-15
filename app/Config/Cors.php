<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins'         => [
            'http://localhost:5173',      // Dev Vite
            'https://tickets.maewalliscorp.org',  // Prod frontend
            'http://tickets.maewalliscorp.org'    // Si pruebas sin HTTPS
        ],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => false,  // Cambia a true si usas cookies/auth
        'allowedHeaders'         => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'                 => 3600,
    ];

    // Perfil específico para API
    public array $api = [
        'allowedOrigins'         => [
            'https://tickets.maewalliscorp.org',
            'http://tickets.maewalliscorp.org',
            'http://localhost:5173'  // Para desarrollo local
        ],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => true,   // JWT cookies si usas Shield
        'allowedHeaders'         => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'                 => 86400,  // 24h cache preflight
    ];
}
