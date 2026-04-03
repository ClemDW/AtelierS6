<?php

namespace photopro\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;

class LogoutAction extends AbstractAction
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            if (isset($data['refreshToken']) && is_string($data['refreshToken'])) {
                // On détruit le refresh token de la BDD
                $this->authRepository->revokeRefreshToken($data['refreshToken']);
            }

            $payload = json_encode([
                'success' => true,
                'message' => 'Déconnecté avec succès'
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur interne']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
