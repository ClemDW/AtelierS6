<?php

use Psr\Container\ContainerInterface;
use photopro\core\domain\entities\auth\AuthServiceInterface;
use photopro\core\application\usecases\AuthService;
use photopro\core\application\ports\spi\repositoryInterfaces\UserRepositoryInterface;
use photopro\infra\repositories\PDOUserRepository;
use photopro\api\actions\SigninAction;
use photopro\api\actions\RefreshTokenAction;
use photopro\api\provider\AuthProviderInterface;
use photopro\api\provider\JwtAuthProvider;
use photopro\api\middlewares\AuthnMiddleware;
use photopro\api\actions\ValidateTokenAction;

return [

    // settings
    "displayErrorDetails" => true,
    "logs.dir" => __DIR__ . "/../../var/logs",
    "env.config" => __DIR__ . "/photopro.db.ini",


    SigninAction::class => function (ContainerInterface $c) {
        return new SigninAction($c->get(AuthProviderInterface::class));
    },

    RefreshTokenAction::class => function (ContainerInterface $c) {
        return new RefreshTokenAction($c->get(AuthProviderInterface::class));
    },

    ValidateTokenAction::class => function (ContainerInterface $c) {
        return new ValidateTokenAction($c->get(AuthProviderInterface::class));
    },

    AuthServiceInterface::class => function (ContainerInterface $c) {
        return new AuthService($c->get(UserRepositoryInterface::class));
    },

    AuthProviderInterface::class => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get("env.config"));
        $secret = $config["auth.jwt.key"] ?? getenv("AUTH_JWT_KEY") ?? null;
        if (!$secret) {
            $secret = "your_secret_key"; 
        }

        return new JwtAuthProvider(
            $c->get(AuthServiceInterface::class),
            $secret,
            "HS256",
            3600,
            86400
        );
    },

    AuthnMiddleware::class => function (ContainerInterface $c) {
        return new AuthnMiddleware($c->get(AuthProviderInterface::class));
    },

    UserRepositoryInterface::class => fn(ContainerInterface $c) => new PDOUserRepository($c->get("auth.pdo")),

    "auth.pdo" => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get("env.config"));
        $dsn = "{$config["driver"]}:host={$config["host"]};dbname={$config["database"]}";
        $user = $config["username"];
        $password = $config["password"];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

];
