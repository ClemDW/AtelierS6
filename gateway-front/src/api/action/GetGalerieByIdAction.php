<?php

declare(strict_types=1);

namespace photopro\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;

class GetGalerieByIdAction
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';

        try {
            $apiResponse = $this->client->request('GET', "/galeries/{$id}");
            $response->getBody()->write($apiResponse->getBody()->getContents());
            return $response
                ->withStatus($apiResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $response->getBody()->write(json_encode([
                'error' => 'Erreur lors de la communication avec le service galerie',
                'details' => $e->getMessage()
            ]));
            return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
        }
    }
}
