<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModifierInfosGalerieAction
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

        $titre = trim((string) ($body['titre'] ?? ''));
        $description = trim((string) ($body['description'] ?? ''));

        if ($titre === '') {
            $response->getBody()->write(json_encode(['error' => 'Le titre est obligatoire']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->serviceGalerie->modifierInfosGalerie($id, $titre, $description);
        } catch (GalerieNotFoundException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'id' => $id,
            'titre' => $titre,
            'description' => $description,
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
