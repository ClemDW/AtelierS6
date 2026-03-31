<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use photopro\api\middlewares\AuthzMiddleware;


return function (App $app): App {

    // ==============================
    // ROUTES API RESTFUL
    // ==============================


    // Route publique
    //$app->post('/signin', SigninAction::class);

    // Routes protégées par AuthzMiddleware


    return $app;
};
