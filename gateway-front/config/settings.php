<?php
declare(strict_types=1);

return [
    'displayErrorDetails' => true,
    'logErrors' => true,
    'logErrorDetails' => true,
    
    'logs.dir' => __DIR__ . '/../var/logs',
    
    'api.galerie' => [
        'base_uri' => 'http://service-galerie.photopro:80', 
        'timeout' => 10.0,
    ],
];