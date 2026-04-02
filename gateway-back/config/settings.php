<?php
declare(strict_types=1);

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use photopro\gateway\Actions\ProxyAction;
use photopro\gateway\Middleware\CorsMiddleware;
use photopro\gateway\Middleware\JwtAuthMiddleware;
use photopro\gateway\Middleware\RoleMiddleware;
use photopro\gateway\Service\ProxyService;

return [
    'displayErrorDetails' => true,

    'gateway.auth.url' => getenv('AUTH_SERVICE_URL') ?: 'http://service-auth.photopro:80',
    'gateway.galerie.url' => getenv('GALERIE_SERVICE_URL') ?: 'http://service-galerie.photopro:80',
    'gateway.storage.url' => getenv('STORAGE_SERVICE_URL') ?: 'http://service-storage.photopro:80',
    'gateway.jwt.secret' => getenv('AUTH_JWT_KEY') ?: 'your_secret_key',
    'gateway.jwt.alg' => getenv('AUTH_JWT_ALGORITHM') ?: 'HS256',

    Client::class => static fn (): Client => new Client(),

    ProxyService::class => static fn (ContainerInterface $c): ProxyService => new ProxyService(
        $c->get(Client::class),
        10
    ),

    ProxyAction::class . '.authRoot' => static fn (ContainerInterface $c): ProxyAction => new ProxyAction(
        $c->get(ProxyService::class),
        $c->get('gateway.auth.url')
    ),
    ProxyAction::class . '.galerieRoot' => static fn (ContainerInterface $c): ProxyAction => new ProxyAction(
        $c->get(ProxyService::class),
        $c->get('gateway.galerie.url')
    ),
    ProxyAction::class . '.galeriePlural' => static fn (ContainerInterface $c): ProxyAction => new ProxyAction(
        $c->get(ProxyService::class),
        $c->get('gateway.galerie.url'),
        'galeries'
    ),
    ProxyAction::class . '.galerieSingle' => static fn (ContainerInterface $c): ProxyAction => new ProxyAction(
        $c->get(ProxyService::class),
        $c->get('gateway.galerie.url'),
        'galerie'
    ),
    ProxyAction::class . '.storageRoot' => static fn (ContainerInterface $c): ProxyAction => new ProxyAction(
        $c->get(ProxyService::class),
        $c->get('gateway.storage.url')
    ),

    CorsMiddleware::class => static fn (): CorsMiddleware => new CorsMiddleware(),
    JwtAuthMiddleware::class => static fn (ContainerInterface $c): JwtAuthMiddleware => new JwtAuthMiddleware(
        $c->get('gateway.jwt.secret'),
        $c->get('gateway.jwt.alg')
    ),
    RoleMiddleware::class => static fn (): RoleMiddleware => new RoleMiddleware(['photographe', 'admin']),
];
