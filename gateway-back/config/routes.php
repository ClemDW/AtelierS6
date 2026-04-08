<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use photopro\gateway\Actions\ProxyAction;
use photopro\gateway\Middleware\JwtAuthMiddleware;
use photopro\gateway\Middleware\RoleMiddleware;

return function (App $app): App {
    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    $authProxy = $container->get(ProxyAction::class . '.authRoot');
    $galerieRootProxy = $container->get(ProxyAction::class . '.galerieRoot');
    $galeriesProxy = $container->get(ProxyAction::class . '.galeriePlural');
    $galerieSingleProxy = $container->get(ProxyAction::class . '.galerieSingle');
    $storageProxy = $container->get(ProxyAction::class . '.storageRoot');

    $app->get('/', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode([
            'service' => 'gateway-back.photopro',
            'status' => 'ok',
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/health', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode(['ok' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Public photo streaming endpoint used by <img src="..."> in front.
    $app->get('/api/back/storage/photos/{id}', function (Request $request, Response $response, array $args) use ($storageProxy): Response {
        return $storageProxy($request, $response, ['path' => 'photos/' . $args['id']]);
    });

    $app->group('/api/back/auth', function (\Slim\Routing\RouteCollectorProxy $group) use ($authProxy) {
        $group->post('/register', function (Request $request, Response $response) use ($authProxy): Response {
            return $authProxy($request, $response, ['path' => 'register']);
        });
        $group->post('/signin', function (Request $request, Response $response) use ($authProxy): Response {
            return $authProxy($request, $response, ['path' => 'signin']);
        });
        $group->post('/refresh', function (Request $request, Response $response) use ($authProxy): Response {
            return $authProxy($request, $response, ['path' => 'refresh']);
        });
        $group->post('/validate', function (Request $request, Response $response) use ($authProxy): Response {
            return $authProxy($request, $response, ['path' => 'validate']);
        });
    });

    $app->group('/api/back', function (\Slim\Routing\RouteCollectorProxy $group) use ($galerieRootProxy, $galeriesProxy, $galerieSingleProxy, $storageProxy, $authProxy) {
        $group->get('/galeries/{id}/complet', function (Request $request, Response $response, array $args) use ($galerieRootProxy): Response {
            return $galerieRootProxy($request, $response, ['path' => 'galeries/' . $args['id'] . '/complet']);
        });
        $group->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/galeries[/{path:.*}]', $galeriesProxy);
        $group->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/galerie[/{path:.*}]', $galerieSingleProxy);
        $group->get('/photographes/{photographeId}/galeries', function (Request $request, Response $response, array $args) use ($galerieRootProxy): Response {
            return $galerieRootProxy($request, $response, ['path' => 'photographes/' . $args['photographeId'] . '/galeries']);
        });
        


        $group->post('/storage/users/{id}/photos', function (Request $request, Response $response, array $args) use ($storageProxy): Response {
            return $storageProxy($request, $response, ['path' => 'users/' . $args['id'] . '/photos']);
        });
        $group->post('/photos/upload/{id}', function (Request $request, Response $response, array $args) use ($storageProxy): Response {
            $forwardArgs = ['path' => 'users/' . $args['id'] . '/photos'];
            return $storageProxy($request, $response, $forwardArgs);
        });
        $group->get('/storage/users/{id}/photos', function (Request $request, Response $response, array $args) use ($storageProxy): Response {
            return $storageProxy($request, $response, ['path' => 'users/' . $args['id'] . '/photos']);
        });
        $group->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/storage[/{path:.*}]', $storageProxy);

        $group->get('/me', function (Request $request, Response $response): Response {
            $claims = $request->getAttribute('jwt.claims', []);
            $response->getBody()->write(json_encode([
                'id' => $claims['sub'] ?? null,
                'email' => $claims['email'] ?? null,
                'role' => $claims['role'] ?? ($claims['roleName'] ?? null),
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json');
        });

    })->add($container->get(RoleMiddleware::class))
      ->add($container->get(JwtAuthMiddleware::class));

    return $app;
};
