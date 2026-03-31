<?php

use Aws\S3\S3Client;
use Psr\Container\ContainerInterface;
use storage\api\actions\UploadAction;
use storage\core\usecases\StorageService;

return [
    'internal_endpoint' => $_ENV['S3_INTERNAL_ENDPOINT'],   // interne à la composition docker, e.g. http://S3.service:8333
    'external_endpoint' => $_ENV['S3_EXTERNAL_ENDPOINT'],   // externe, e.g. http://localhost:8333 / http//docketu.iutnc.univ-lorraine.fr:8333
    'region' => $_ENV['S3_REGION'] ?? 'seaweedFS',
    'key' => $_ENV['S3_ACCESS_KEY'],
    'secret' => $_ENV['S3_SECRET_KEY'],
    'bucket' => $_ENV['S3_BUCKET'],

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
    return new StorageService($c->get('S3_internal_client'),$c->get('S3_external_client'), $c->get('bucket'));
    },
    UploadAction::class => function(ContainerInterface $c){
    return new UploadAction($c->get(StorageService::class));
    }


];
