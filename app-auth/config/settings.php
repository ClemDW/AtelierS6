<?php

use Psr\Container\ContainerInterface;
use photopro\api\actions\SignupAction;
use photopro\core\application\ports\api\service\AuthnServiceInterface;

use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use photopro\infra\repositories\PDOAuthRepository;
use photopro\core\application\ports\api\jwt\JwtManagerInterface;
use photopro\infra\jwt\JwtManager;
use photopro\api\actions\SigninAction;
use photopro\api\actions\RefreshTokenAction;
use photopro\api\provider\AuthProviderInterface;
use photopro\api\provider\JwtAuthProvider;
use photopro\api\middlewares\AuthnMiddleware;
use photopro\api\actions\ValidateTokenAction;
use photopro\core\application\usecases\AuthnService;
use photopro\core\application\ports\spi\EventPublisherInterface;
use photopro\infra\RabbitMqPublisher;

return [

    // settings
    "displayErrorDetails" => true,
    "logs.dir" => __DIR__ . "/../../var/logs",
    "env.config" => __DIR__ . "/photopro.db.ini",


    SigninAction::class => function (ContainerInterface $c) {
        return new SigninAction($c->get(AuthProviderInterface::class));
    },

    SignupAction::class => function (ContainerInterface $c) {
        return new SignupAction($c->get(AuthProviderInterface::class));
    },

    RefreshTokenAction::class => function (ContainerInterface $c) {
        return new RefreshTokenAction($c->get(AuthProviderInterface::class));
    },

    ValidateTokenAction::class => function (ContainerInterface $c) {
        return new ValidateTokenAction($c->get(AuthProviderInterface::class));
    },

    EventPublisherInterface::class => function (ContainerInterface $c) {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
        $user = getenv('RABBITMQ_USER') ?: 'photopro';
        $pass = getenv('RABBITMQ_PASS') ?: 'photopro';
        $exchange = 'photopro.events'; // Exchange topic
        
        return new RabbitMqPublisher($host, $port, $user, $pass, $exchange);
    },

    AuthnServiceInterface::class => function (ContainerInterface $c) {
        return new AuthnService(
            $c->get(AuthRepositoryInterface::class),
            $c->get(EventPublisherInterface::class)
        );
    },

    JwtManagerInterface::class => function (ContainerInterface $c) {
        $secret = $_ENV['AUTH_JWT_KEY'] ?? getenv("AUTH_JWT_KEY");
        
        if (empty($secret)) {
            throw new \RuntimeException("La clé secrète JWT (AUTH_JWT_KEY) n'est pas définie dans le fichier .env !");
        }
        
        return new JwtManager($secret);
    },

    AuthProviderInterface::class => function (ContainerInterface $c) {
        return new JwtAuthProvider(
            $c->get(AuthnServiceInterface::class),
            $c->get(JwtManagerInterface::class),
            $c->get(AuthRepositoryInterface::class)
        );
    },

    AuthnMiddleware::class => function (ContainerInterface $c) {
        return new AuthnMiddleware($c->get(AuthProviderInterface::class));
    },

    AuthRepositoryInterface::class => fn(ContainerInterface $c) => new PDOAuthRepository($c->get("auth.pdo")),

    "auth.pdo" => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get("env.config"));
        $dsn = "{$config["driver"]}:host={$config["host"]};dbname={$config["database"]}";
        $user = $config["username"];
        $password = $config["password"];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

];
