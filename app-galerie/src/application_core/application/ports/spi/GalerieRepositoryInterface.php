<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi;

interface GalerieRepositoryInterface
{
    public function getGaleriesPublic(): array;
    public function ajouterPhotoGalerie(string $galerieId, string $photoId): void;
    public function supprimerPhotoGalerie(string $galerieId, string $photoId): void;
    public function publierGalerie(string $galerieId): void;
    public function depublierGalerie(string $galerieId): void;
}