<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModifierPublicationGalerieAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $body = $request->getParsedBody();

        if (!isset($body['estPubliee'])) {
            $response->getBody()->write(json_encode(['error' => 'Champ manquant : estPubliee']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $estPubliee = (bool) $body['estPubliee'];

        try {
            if ($estPubliee) {
                $this->serviceGalerie->publierGalerie($id);
            } else {
                $this->serviceGalerie->depublierGalerie($id);
            }
        } catch (GalerieNotFoundException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode(['estPubliee' => $estPubliee]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
