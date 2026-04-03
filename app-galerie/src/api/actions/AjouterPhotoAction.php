<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AjouterPhotoAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $galerieId = $args['id'];
        $body = $request->getParsedBody();

        if (empty($body['photoId'])) {
            $response->getBody()->write(json_encode(['error' => 'Champ manquant : photoId']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->serviceGalerie->ajouterPhoto($galerieId, $body['photoId']);
        } catch (GalerieNotFoundException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
    }
}
