<?php
declare(strict_types=1);

return [
    'displayErrorDetails' => true,
    'logErrors' => true,
    'logErrorDetails' => true,
    
    'logs.dir' => __DIR__ . '/../var/logs',
    
    'api.galerie' => [
        'base_uri' => 'http://app-galerie:80',
        'timeout' => 10.0,
    ],
];
