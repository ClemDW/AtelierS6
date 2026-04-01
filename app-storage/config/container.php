<?php

use Aws\S3\S3Client;
use Psr\Container\ContainerInterface;
use storage\api\actions\UploadAction;
use storage\api\actions\GetPhotoAction;
use storage\core\usecases\StorageService;

return [
    'internal_endpoint' => $_ENV['S3_INTERNAL_ENDPOINT'] ?? getenv('S3_INTERNAL_ENDPOINT') ?? 'http://s3.service:8333',
    'external_endpoint' => $_ENV['S3_EXTERNAL_ENDPOINT'] ?? getenv('S3_EXTERNAL_ENDPOINT') ?? 'http://localhost:8333',
    'region' => $_ENV['S3_REGION'] ?? getenv('S3_REGION') ?? 'seaweedFS',
    'key' => $_ENV['S3_ACCESS_KEY'] ?? getenv('S3_ACCESS_KEY') ?? 'some_access_key',
    'secret' => $_ENV['S3_SECRET_KEY'] ?? getenv('S3_SECRET_KEY') ?? 'some_secret_key',
    'bucket' => $_ENV['S3_BUCKET'] ?? getenv('S3_BUCKET') ?? 'photopro',

    // Base de données
    PDO::class => function(ContainerInterface $c) {
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'galerie.db';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432';
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'galeriedb';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'admin';
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? 'admin';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
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
    UploadAction::class => function(ContainerInterface $c){
    return new UploadAction($c->get(StorageService::class));
    },
    GetPhotoAction::class => function(ContainerInterface $c){
    return new GetPhotoAction($c->get(StorageService::class));
    }


];
