<?php
declare(strict_types=1);

use storage\api\actions\PhotoUploadAction;
use storage\core\StorageService;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../../env/storage.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env', 'storage.env');
    $dotenv->load();
}
$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../config/container.php' );
$container = $builder->build();

// ── Application Slim ──────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');



$app->post('/users/{id}/photos', \storage\api\actions\UploadAction::class);
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write('Hello World!');
    return $response;
});


$app->run();

