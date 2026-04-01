<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use photopro\api\actions\CreerGalerieAction;
use photopro\api\actions\AfficherGalerieAction;
use photopro\api\actions\ListeGalerieAction;


return function (App $app): App {

    // ==============================
    // ROUTES API RESTFUL
    // ==============================

    $app->get('/galeries', ListeGalerieAction::class);
    $app->get('/galeries/{id}', AfficherGalerieAction::class);
    $app->options('/galeries', function (Request $request, Response $response) {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withStatus(204);
    });
    $app->post('/galeries', CreerGalerieAction::class);

    return $app;
};
