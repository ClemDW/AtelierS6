<?php

declare(strict_types=1);

namespace photopro\api\actions;

use photopro\core\application\ports\api\dto\CredentialsDTO;
use photopro\core\application\ports\api\dto\SignupDTO;
use photopro\api\provider\AuthProviderInterface;
use photopro\api\provider\exceptions\AuthProviderInvalidCredentialsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use photopro\core\domain\exceptions\EmailAlreadyExistsException;
use photopro\core\domain\exceptions\InvalidInputException;
use photopro\api\actions\AbstractAction;

final class SignupAction extends AbstractAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new HttpBadRequestException($request, 'Invalid payload');
        }

        $email = isset($data['email']) ? trim((string)$data['email']) : '';
        $password = isset($data['password']) ? (string)$data['password'] : '';
        $name = isset($data['name']) ? trim((string)$data['name']) : '';
        $passwordConfirmation = isset($data['password_confirmation']) ? (string)$data['password_confirmation'] : null;

        if ($email === '' || $password === '' || $name === '') {
            throw new HttpBadRequestException($request, 'Email, password and name are required');
        }

        if ($passwordConfirmation !== null && $password !== $passwordConfirmation) {
            throw new HttpBadRequestException($request, 'Passwords do not match');
        }

        $signupDto = new SignupDTO($email, $password, $name);
        $credentials = new CredentialsDTO($email, $password);

        try {
            // Créer l'utilisateur (le rôle ayant été retiré)
            $profile = $this->authProvider->signup($signupDto);
            
            // Connecter immédiatement pour fournir les tokens
            $authDTO = $this->authProvider->signin($credentials);

            $payload = [
                'success' => true,
                'message' => 'Account created successfully',
                'auth' => $authDTO->toArray(),
            ];

            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (InvalidInputException | \InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (EmailAlreadyExistsException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(409);
        } catch (\RuntimeException $e) {
            // duplicates or custom runtime errors -> 400
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Unexpected error during signup'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
