<?php

declare(strict_types=1);

namespace photopro\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;

class GetGalerieByCodeAction
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();

        try {
            $apiResponse = $this->client->request('POST', '/galeries/code', [
                'json' => $body,
            ]);
            $response->getBody()->write($apiResponse->getBody()->getContents());
            return $response
                ->withStatus($apiResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorBody = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : json_encode([
                    'error' => 'Erreur lors de la communication avec le service galerie',
                    'details' => $e->getMessage()
                ]);
            $response->getBody()->write($errorBody);
            return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
        }
    }
}
