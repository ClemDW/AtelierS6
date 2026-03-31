<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api;

interface ServiceGalerieInterface
{
    public function getGaleriesPublic(): array;
}