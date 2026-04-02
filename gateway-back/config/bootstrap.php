<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use photopro\gateway\Middleware\CorsMiddleware;

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/settings.php');
$container = $builder->build();

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(CorsMiddleware::class);

$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), false, false);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

$app = (require_once __DIR__ . '/routes.php')($app);

return $app;
