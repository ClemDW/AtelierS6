<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\application\ports\api\dtos\CreerGalerieDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreerGalerieAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();

        $champsRequis = ['photographeId', 'typeGalerie', 'titre', 'description', 'estPubliee', 'modeMiseEnPage'];
        foreach ($champsRequis as $champ) {
            if (!isset($body[$champ])) {
                $response->getBody()->write(json_encode(['error' => "Champ manquant : $champ"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $dto = new CreerGalerieDTO(
            $body['photographeId'],
            $body['typeGalerie'],
            $body['titre'],
            $body['description'],
            (bool) $body['estPubliee'],
            $body['modeMiseEnPage'],
            $body['emailsClients'] ?? [],
            $body['photos'] ?? []
        );

        $galerie = $this->serviceGalerie->creerGalerie($dto);

        $response->getBody()->write(json_encode($galerie));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}