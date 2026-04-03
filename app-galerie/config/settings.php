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
use photopro\api\actions\ModifierPublicationGalerieAction;
use photopro\api\actions\AjouterPhotoAction;
use photopro\api\actions\RetirerPhotoAction;
use photopro\api\actions\ListeGalerieParPhotographeAction;
use photopro\api\actions\ModifierMiseEnPageAction;

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
        $host     = $_ENV['POSTGRES_HOST']     ?? 'galerie.db';
        $port     = $_ENV['POSTGRES_PORT']     ?? '5432';
        $dbname   = $_ENV['POSTGRES_DB']       ?? 'galeriedb';
        $user     = $_ENV['POSTGRES_USER']     ?? 'admin';
        $password = $_ENV['POSTGRES_PASSWORD'] ?? 'admin';
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

    ModifierPublicationGalerieAction::class => function (ContainerInterface $c): ModifierPublicationGalerieAction {
        return new ModifierPublicationGalerieAction($c->get(ServiceGalerieInterface::class));
    },

    AjouterPhotoAction::class => function (ContainerInterface $c): AjouterPhotoAction {
        return new AjouterPhotoAction($c->get(ServiceGalerieInterface::class));
    },

    RetirerPhotoAction::class => function (ContainerInterface $c): RetirerPhotoAction {
        return new RetirerPhotoAction($c->get(ServiceGalerieInterface::class));
    },

    ListeGalerieParPhotographeAction::class => function (ContainerInterface $c): ListeGalerieParPhotographeAction {
        return new ListeGalerieParPhotographeAction($c->get(ServiceGalerieInterface::class));
    },

    ModifierMiseEnPageAction::class => function (ContainerInterface $c): ModifierMiseEnPageAction {
        return new ModifierMiseEnPageAction($c->get(ServiceGalerieInterface::class));
    },
];
