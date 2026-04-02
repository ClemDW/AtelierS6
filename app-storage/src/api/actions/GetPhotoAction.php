<?php

namespace storage\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use storage\core\usecases\StorageService;

class GetPhotoAction
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;

        if (!$id) {
            $response->getBody()->write(json_encode(['error' => 'ID de photo manquant']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            // Récupère le flux S3 et le type mime via le service
            $photoData = $this->storageService->getPhotoStreamAndMimeType($id);
            $stream = $photoData['stream'];
            $mimeType = $photoData['mime_type'];

            // Renvoie le flux avec les bons headers
            return $response
                ->withHeader('Content-Type', $mimeType)
                ->withBody($stream);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}
