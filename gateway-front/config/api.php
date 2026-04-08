<?php

declare(strict_types=1);

use OpenApi\Annotations\Get;
use Psr\Container\ContainerInterface;
use photopro\gateway\api\action\GetGaleriesAction;
use photopro\gateway\api\action\GetGalerieByIdAction;
use photopro\gateway\api\action\GetGalerieAccessCodeAction;
use photopro\gateway\api\action\GetGalerieByCodeAction;

return [
    GetGaleriesAction::class => fn(ContainerInterface $c) => new GetGaleriesAction($c->get('client.galerie')),
    GetGalerieByIdAction::class => fn(ContainerInterface $c) => new GetGalerieByIdAction($c->get('client.galerie')),
    GetGalerieAccessCodeAction::class => fn(ContainerInterface $c) => new GetGalerieAccessCodeAction($c->get('client.galerie')),
    GetGalerieByCodeAction::class => fn(ContainerInterface $c) => new GetGalerieByCodeAction($c->get('client.galerie')),
];
