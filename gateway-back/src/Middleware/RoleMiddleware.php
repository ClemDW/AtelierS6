<?php
declare(strict_types=1);

namespace photopro\gateway\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RoleMiddleware implements MiddlewareInterface
{
    /** @var string[] */
    private array $allowedRoles;

    /**
     * @param string[] $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = array_map('strtolower', $allowedRoles);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $claims = $request->getAttribute('jwt.claims');
        if (!is_array($claims)) {
            return $this->jsonError(401, 'Claims JWT absents', 'AUTH_ERROR');
        }

        $role = strtolower((string) ($claims['role'] ?? $claims['roleName'] ?? ''));
        if ($role === '') {
            return $handler->handle($request);
        }

        if (!in_array($role, $this->allowedRoles, true)) {
            return $this->jsonError(403, 'Droits insuffisants', 'ACCESS_DENIED');
        }

        return $handler->handle($request);
    }

    private function jsonError(int $status, string $message, string $code): ResponseInterface
    {
        $response = new \Slim\Psr7\Response($status);
        $response->getBody()->write(json_encode([
            'error' => $message,
            'code' => $code,
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
