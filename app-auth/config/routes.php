<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {
    $app->get("/", function (Request $request, Response $response) {
        $response->getBody()->write("Auth Service API");
        return $response;
    });


    return $app;
};
