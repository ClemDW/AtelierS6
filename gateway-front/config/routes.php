<?php
declare(strict_types=1);

use Slim\App;
use photopro\gateway\api\middlewares\Cors;
use photopro\gateway\api\action\GetGaleriesAction;
use photopro\gateway\api\action\GetGalerieByIdAction;
use photopro\gateway\api\action\GetGalerieAccessCodeAction;
use photopro\gateway\api\action\GetGalerieByCodeAction;
use photopro\gateway\api\action\GetGaleriesParPhotographeAction;

return function (App $app) {
    // Appliquer le middleware CORS 
    $app->add(Cors::class);

    // Routes publiques du Gateway Front
    $app->get('/galeries', GetGaleriesAction::class);
    $app->get('/galeries/{id}', GetGalerieByIdAction::class);
    $app->get('/galeries/{id}/code', GetGalerieAccessCodeAction::class);
    $app->post('/galeries/code', GetGalerieByCodeAction::class);
};
