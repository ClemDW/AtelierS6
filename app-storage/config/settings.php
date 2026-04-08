<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;




// === Authn / Authz ===
/*
use photopro\core\application\usecases\photoproAuthnService;
use photopro\core\application\ports\api\photoproAuthnServiceInterface;
use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use photopro\infra\repositories\AuthRepository;
use photopro\api\provider\AuthProviderInterface;
use photopro\api\provider\jwt\JwtAuthProvider;
use photopro\api\provider\jwt\JwtManagerInterface;
use photopro\api\provider\jwt\JwtManager;
use photopro\api\middlewares\AuthnMiddleware;
*/

return [

    // ==============================
    // Configuration générale
    // ==============================
    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../var/logs',
];
