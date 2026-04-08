<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChoisirPhotoEnteteAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $body = $request->getParsedBody() ?? [];
        $photoIdRaw = $body['photoId'] ?? $body['photo_id'] ?? null;
        $photoId = $photoIdRaw === null ? null : trim((string) $photoIdRaw);
        if ($photoId === '') {
            $photoId = null;
        }

        try {
            $this->serviceGalerie->definirPhotoEntete($id, $photoId);
        } catch (GalerieNotFoundException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'id' => $id,
            'photoEnteteId' => $photoId,
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
