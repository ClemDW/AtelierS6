<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi;

use \photopro\core\domain\entities\Galerie;

interface GalerieRepositoryInterface
{
    public function getGaleriesPublic(): array;
    public function getGaleriesParPhotographe(string $photographeId): array;
    public function getGalerieByCodeAcces(string $code): ?Galerie;
    public function ajouterPhotoGalerie(string $galerieId, string $photoId): void;
    public function supprimerPhotoGalerie(string $galerieId, string $photoId): bool;
    public function publierGalerie(string $galerieId): void;
    public function depublierGalerie(string $galerieId): void;
    public function modifierMiseEnPage(string $galerieId, string $miseEnPage): void;
    public function getGalerieById(string $id): ?Galerie;
    public function creerGalerie(Galerie $galerie): Galerie;
    public function supprimerGalerie(string $id): void;
}