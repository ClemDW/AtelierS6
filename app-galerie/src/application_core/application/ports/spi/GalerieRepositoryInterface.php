<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi;

use \photopro\core\domain\entities\Galerie;

interface GalerieRepositoryInterface
{
    public function getGaleriesPublic(): array;
    public function getGalerieById(string $id): ?Galerie;
    public function creerGalerie(Galerie $galerie): Galerie;
}