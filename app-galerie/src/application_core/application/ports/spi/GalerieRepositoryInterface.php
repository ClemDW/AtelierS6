<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi;

interface GalerieRepositoryInterface
{
    public function getGaleriesPublic(): array;
}