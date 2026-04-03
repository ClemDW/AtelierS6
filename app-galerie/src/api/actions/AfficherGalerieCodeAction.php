<?php

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AfficherGalerieCodeAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (empty($body['code'])) {
            $response->getBody()->write(json_encode(['error' => 'Code d\'accès manquant']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        try {
            $galerie = $this->serviceGalerie->getGalerieByCodeAcces($body['code']);
        } catch (GalerieNotFoundException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        $response->getBody()->write(json_encode($galerie));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}