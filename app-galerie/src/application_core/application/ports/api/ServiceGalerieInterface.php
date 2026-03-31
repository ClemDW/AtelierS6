<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;

interface ServiceGalerieInterface
{
    public function getGaleriesPublic(): array;
    public function getGalerieAffiche(string $id): ?GalerieAfficheDTO;
}