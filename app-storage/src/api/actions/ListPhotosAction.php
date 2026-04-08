<?php

declare(strict_types=1);

namespace storage\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use storage\core\usecases\StorageService;

class ListPhotosAction
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $ownerId = $args['id'] ?? null;

        if (!$ownerId) {
            $response->getBody()->write(json_encode(['error' => 'ID du propriétaire manquant']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $photos = $this->storageService->listPhotosByOwner((string) $ownerId);
        $response->getBody()->write(json_encode($photos));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
