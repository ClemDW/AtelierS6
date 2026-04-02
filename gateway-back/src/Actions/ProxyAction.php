<?php
declare(strict_types=1);

namespace photopro\gateway\Actions;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use photopro\gateway\Service\ProxyService;

final class ProxyAction
{
    public function __construct(
        private readonly ProxyService $proxyService,
        private readonly string $baseUri,
        private readonly string $prefix = ''
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $path = isset($args['path']) ? (string) $args['path'] : '';
        $targetPath = $this->buildTargetPath($path);

        try {
            $result = $this->proxyService->forward($request, $this->baseUri, $targetPath);
        } catch (GuzzleException) {
            $response->getBody()->write(json_encode([
                'error' => 'Service distant indisponible',
                'code' => 'UPSTREAM_UNAVAILABLE',
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(503);
        } catch (\Throwable) {
            $response->getBody()->write(json_encode([
                'error' => 'Erreur de routage gateway',
                'code' => 'GATEWAY_ERROR',
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(502);
        }

        $response->getBody()->write($result['body']);
        foreach ($result['headers'] as $name => $values) {
            if (strtolower($name) === 'transfer-encoding') {
                continue;
            }

            $response = $response->withHeader($name, $values);
        }

        return $response->withStatus($result['status']);
    }

    private function buildTargetPath(string $path): string
    {
        $prefix = trim($this->prefix, '/');
        $tail = trim($path, '/');

        if ($prefix === '') {
            return '/' . $tail;
        }

        if ($tail === '') {
            return '/' . $prefix;
        }

        return '/' . $prefix . '/' . $tail;
    }
}
