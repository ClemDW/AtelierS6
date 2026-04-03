<?php
declare(strict_types=1);

use Slim\App;
use photopro\gateway\api\middlewares\Cors;
use photopro\gateway\api\action\GetGaleriesAction;
use photopro\gateway\api\action\GetGalerieByIdAction;

return function (App $app) {
    // Appliquer le middleware CORS globalement
    $app->add(Cors::class);
    
    // Routes publiques du Gateway Front
    $app->get('/galeries', GetGaleriesAction::class);
    $app->get('/galeries/{id}', GetGalerieByIdAction::class);
};
