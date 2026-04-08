<?php

use Aws\S3\S3Client;
use Psr\Container\ContainerInterface;
use storage\api\actions\UploadAction;
use storage\api\actions\GetPhotoAction;
use storage\api\actions\ListPhotosAction;
use storage\core\usecases\StorageService;
use storage\infra\messaging\PhotoUploadedPublisher;

return [
    'internal_endpoint' => $_ENV['S3_INTERNAL_ENDPOINT'] ?? getenv('S3_INTERNAL_ENDPOINT') ?? 'http://s3.service:8333',
    'external_endpoint' => $_ENV['S3_EXTERNAL_ENDPOINT'] ?? getenv('S3_EXTERNAL_ENDPOINT') ?? 'http://localhost:8333',
    'region' => $_ENV['S3_REGION'] ?? getenv('S3_REGION') ?? 'seaweedFS',
    'key' => $_ENV['S3_ACCESS_KEY'] ?? getenv('S3_ACCESS_KEY') ?? 'some_access_key',
    'secret' => $_ENV['S3_SECRET_KEY'] ?? getenv('S3_SECRET_KEY') ?? 'some_secret_key',
    'bucket' => $_ENV['S3_BUCKET'] ?? getenv('S3_BUCKET') ?? 'photopro',
    'rabbitmq.host' => $_ENV['RABBITMQ_HOST'] ?? getenv('RABBITMQ_HOST') ?? 'rabbitmq',
    'rabbitmq.port' => (int) ($_ENV['RABBITMQ_PORT'] ?? getenv('RABBITMQ_PORT') ?? 5672),
    'rabbitmq.user' => $_ENV['RABBITMQ_USER'] ?? getenv('RABBITMQ_USER') ?? 'photopro',
    'rabbitmq.pass' => $_ENV['RABBITMQ_PASS'] ?? getenv('RABBITMQ_PASS') ?? 'photopro',
    'rabbitmq.exchange' => $_ENV['RABBITMQ_EXCHANGE'] ?? getenv('RABBITMQ_EXCHANGE') ?? 'photopro.events',
    'rabbitmq.routing_key.photo_uploaded' => $_ENV['RABBITMQ_ROUTING_KEY_PHOTO_UPLOADED'] ?? getenv('RABBITMQ_ROUTING_KEY_PHOTO_UPLOADED') ?? 'photo.uploaded',

    // Base de données
    PDO::class => function(ContainerInterface $c) {
        $host = getenv('DB_HOST') ?: 'galerie.db';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'galeriedb';
        $user = getenv('DB_USER') ?: 'admin';
        $pass = getenv('DB_PASS') ?: 'admin';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        try {
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            throw new \PDOException("Erreur de connexion : " . $e->getMessage() . " (User used: $user)");
        }
    },

    'S3_internal_client'=> function(ContainerInterface $c){
        $client= new S3Client([
            'region'   => $c->get('region'),
            'endpoint' => $c->get('internal_endpoint'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $c->get('key'),
                'secret' => $c->get('secret'),
            ],
        ]);
        return $client;
    },
    'S3_external_client'=> function(ContainerInterface $c){
        $client= new S3Client([
            'region'   => $c->get('region'),
            'endpoint' => $c->get('external_endpoint'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $c->get('key'),
                'secret' => $c->get('secret'),
            ],
        ]);
        return $client;
    },
    StorageService::class => function(ContainerInterface $c){
    return new StorageService($c->get('S3_internal_client'),$c->get('S3_external_client'), $c->get('bucket'), $c->get(PDO::class));
    },
    PhotoUploadedPublisher::class => function (ContainerInterface $c) {
        return new PhotoUploadedPublisher(
            $c->get('rabbitmq.host'),
            $c->get('rabbitmq.port'),
            $c->get('rabbitmq.user'),
            $c->get('rabbitmq.pass'),
            $c->get('rabbitmq.exchange'),
            $c->get('rabbitmq.routing_key.photo_uploaded')
        );
    },
    UploadAction::class => function(ContainerInterface $c){
    return new UploadAction($c->get(StorageService::class), $c->get(PhotoUploadedPublisher::class));
    },
    GetPhotoAction::class => function(ContainerInterface $c){
    return new GetPhotoAction($c->get(StorageService::class));
    },
    ListPhotosAction::class=> function(ContainerInterface $c){
    return new ListPhotosAction($c->get(StorageService::class));
    }


];
