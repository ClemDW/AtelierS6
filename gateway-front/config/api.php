<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use photopro\gateway\api\action\GetGaleriesAction;
use photopro\gateway\api\action\GetGalerieByIdAction;

return [
    GetGaleriesAction::class => fn(ContainerInterface $c) => new GetGaleriesAction($c->get('client.galerie')),
    GetGalerieByIdAction::class => fn(ContainerInterface $c) => new GetGalerieByIdAction($c->get('client.galerie'))
];
