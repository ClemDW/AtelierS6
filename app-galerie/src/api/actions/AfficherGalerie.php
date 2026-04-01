<?php

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AfficherGalerie
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        if (empty($id)) {
            $response->getBody()->write(json_encode(['error' => 'ID de galerie manquant']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $galerie = $this->serviceGalerie->getGalerieAffiche($id);
        if ($galerie === null) {
            $response->getBody()->write(json_encode(['error' => 'Galerie not found']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        $response->getBody()->write(json_encode($galerie));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}

