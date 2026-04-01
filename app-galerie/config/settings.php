<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;
use photopro\core\application\usecases\ServiceGalerie;
use photopro\infra\GalerieRepository;
use photopro\api\actions\ListeGalerieAction;
use photopro\api\actions\AfficherGalerieAction;
use photopro\api\actions\CreerGalerieAction;

return [

    // ==============================
    // Configuration générale
    // ==============================
    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../var/logs',

    // ==============================
    // Base de données
    // ==============================
    PDO::class => function (): PDO {
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $port     = $_ENV['DB_PORT'] ?? '3306';
        $dbname   = $_ENV['DB_NAME'] ?? 'photopro';
        $user     = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // ==============================
    // Repository
    // ==============================
    GalerieRepository::class => function (ContainerInterface $c): GalerieRepository {
        return new GalerieRepository($c->get(PDO::class));
    },

    GalerieRepositoryInterface::class => function (ContainerInterface $c): GalerieRepository {
        return $c->get(GalerieRepository::class);
    },

    // ==============================
    // Service
    // ==============================
    ServiceGalerieInterface::class => function (ContainerInterface $c): ServiceGalerie {
        return new ServiceGalerie($c->get(GalerieRepository::class));
    },

    // ==============================
    // Actions
    // ==============================
    ListeGalerieAction::class => function (ContainerInterface $c): ListeGalerieAction {
        return new ListeGalerieAction($c->get(ServiceGalerieInterface::class));
    },

    AfficherGalerieAction::class => function (ContainerInterface $c): AfficherGalerieAction {
        return new AfficherGalerieAction($c->get(ServiceGalerieInterface::class));
    },

    CreerGalerieAction::class => function (ContainerInterface $c): CreerGalerieAction {
        return new CreerGalerieAction($c->get(ServiceGalerieInterface::class));
    },
];
