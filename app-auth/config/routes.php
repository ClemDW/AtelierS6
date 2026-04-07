<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use photopro\api\actions\SignupAction;
use photopro\api\actions\SigninAction;
use photopro\api\actions\RefreshTokenAction;
use photopro\api\actions\ValidateTokenAction;
use \photopro\api\actions\LogoutAction;


return function (App $app): App {

    $app->post('/register', SignupAction::class)->setName('auth.register');
    $app->post('/signin', SigninAction::class)->setName('auth.signin');
    $app->post('/refresh', RefreshTokenAction::class)->setName('auth.refresh');
    $app->post('/logout', LogoutAction::class)->setName('auth.logout');

    $app->get('/validate', ValidateTokenAction::class)->setName('tokens.validate');

    return $app;
};
