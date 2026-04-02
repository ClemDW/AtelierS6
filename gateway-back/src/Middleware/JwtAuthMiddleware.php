<?php
declare(strict_types=1);

namespace photopro\gateway\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $secret,
        private readonly string $algorithm = 'HS256'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractBearerToken($request);
        if ($token === null) {
            return $this->jsonError(401, 'Token JWT manquant');
        }

        try {
            $claims = $this->decodeAndValidateToken($token);

            if (($claims['type'] ?? 'access') !== 'access') {
                return $this->jsonError(401, 'Type de token invalide');
            }

            $request = $request->withAttribute('jwt.claims', $claims);
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return $this->jsonError(401, 'Token JWT invalide ou expire');
        }
    }

    private function extractBearerToken(ServerRequestInterface $request): ?string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAndValidateToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('JWT malformed');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!is_array($header) || !is_array($payload)) {
            throw new \RuntimeException('JWT payload invalid');
        }

        if (($header['alg'] ?? null) !== $this->algorithm) {
            throw new \RuntimeException('JWT algorithm mismatch');
        }

        $expectedSignature = $this->base64UrlEncode(hash_hmac(
            'sha256',
            $encodedHeader . '.' . $encodedPayload,
            $this->secret,
            true
        ));

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            throw new \RuntimeException('JWT signature mismatch');
        }

        $now = time();
        if (isset($payload['exp']) && is_numeric($payload['exp']) && (int) $payload['exp'] < $now) {
            throw new \RuntimeException('JWT expired');
        }

        return $payload;
    }

    private function base64UrlDecode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            throw new \RuntimeException('JWT base64 decode failed');
        }

        return $decoded;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function jsonError(int $status, string $message): ResponseInterface
    {
        $response = new \Slim\Psr7\Response($status);
        $response->getBody()->write(json_encode([
            'error' => $message,
            'code' => 'AUTH_ERROR',
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
