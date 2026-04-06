<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface;
use photopro\gateway\api\middlewares\Cors;

return [

    Cors::class => fn() => new Cors(),

    'client.galerie' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.galerie']['base_uri'],
            'timeout' => $settings['api.galerie']['timeout'],
            'http_errors' => false,
        ]);
    },
];
