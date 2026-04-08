<?php
declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\ServiceGalerieInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListePhotosParPhotographeAction
{
    private ServiceGalerieInterface $serviceGalerie;

    public function __construct(ServiceGalerieInterface $serviceGalerie)
    {
        $this->serviceGalerie = $serviceGalerie;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $photographeId = $args['photographeId'];
        $photos = $this->serviceGalerie->getPhotosParPhotographe($photographeId);
        $response->getBody()->write(json_encode($photos));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
