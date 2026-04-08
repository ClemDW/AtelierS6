<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use photopro\api\actions\CreerGalerieAction;
use photopro\api\actions\AfficherGalerieAction;
use photopro\api\actions\ListeGalerieAction;
use photopro\api\actions\ModifierInfosGalerieAction;
use photopro\api\actions\ModifierPublicationGalerieAction;
use photopro\api\actions\AjouterEmailClientAction;
use photopro\api\actions\ChoisirPhotoEnteteAction;
use photopro\api\actions\AjouterPhotoAction;
use photopro\api\actions\RetirerPhotoAction;
use photopro\api\actions\ListeGalerieParPhotographeAction;
use photopro\api\actions\ModifierMiseEnPageAction;
use photopro\api\actions\RecupererCodeAccesAction;
use photopro\api\actions\AfficherGalerieCodeAction;


return function (App $app): App {

    // ==============================
    // ROUTES API RESTFUL
    // ==============================

    $app->get('/galeries', ListeGalerieAction::class);
    $app->get('/photographes/{photographeId}/galeries', ListeGalerieParPhotographeAction::class);
    $app->get('/galeries/{id}', AfficherGalerieAction::class);
    $app->get('/galeries/{id}/code', RecupererCodeAccesAction::class);
    $app->options('/galeries', function (Request $request, Response $response) {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withStatus(204);
    });
    $app->post('/galeries', CreerGalerieAction::class);
    $app->patch('/galeries/{id}', ModifierInfosGalerieAction::class);
    $app->patch('/galeries/{id}/publication', ModifierPublicationGalerieAction::class);
    $app->post('/galeries/{id}/invitations', AjouterEmailClientAction::class);
    $app->patch('/galeries/{id}/photo-entete', ChoisirPhotoEnteteAction::class);
    $app->patch('/galeries/{id}/mise-en-page', ModifierMiseEnPageAction::class);
    $app->post('/galeries/{id}/photos', AjouterPhotoAction::class);
    $app->delete('/galeries/{id}/photos/{photoId}', RetirerPhotoAction::class);
    $app->delete('/galeries/{id}', photopro\api\actions\SupprimerGalerieAction::class);
    $app->post('/galeries/code', AfficherGalerieCodeAction::class);

    return $app;
};
