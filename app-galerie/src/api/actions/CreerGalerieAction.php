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

        // Récupération avec valeurs par défaut pour éviter les erreurs 400
        $photographeId = $body['photographeId'] ?? $body['photographe_id'] ?? '00000000-0000-0000-0000-000000000000';
        $typeGalerie   = $body['typeGalerie']   ?? $body['type_galerie']   ?? 'PRIVEE';
        $titre         = $body['titre']         ?? 'Nouvelle Galerie';
        $description   = $body['description']   ?? '';
        $estPubliee    = (bool) ($body['estPubliee'] ?? $body['est_publiee'] ?? false);
        $modeMiseEnPage = $body['modeMiseEnPage'] ?? $body['mode_mise_en_page'] ?? 'standard';
        
        $dto = new CreerGalerieDTO(
            $photographeId,
            $typeGalerie,
            $titre,
            $description,
            $estPubliee,
            $modeMiseEnPage,
            $body['emailsClients'] ?? $body['emails_clients'] ?? [],
            $body['photos'] ?? []
        );

        $galerie = $this->serviceGalerie->creerGalerie($dto);

        $response->getBody()->write(json_encode($galerie));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}