<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;
use photopro\core\application\ports\api\dtos\CreerGalerieDTO;

interface ServiceGalerieInterface
{
    public function getGaleriesPublic(): array;
    public function getGalerieAffiche(string $id): ?GalerieAfficheDTO;
    public function creerGalerie(CreerGalerieDTO $dto): GalerieAfficheDTO;
    public function ajouterPhoto(string $galerieId, string $photoId): void;
    public function supprimerPhoto(string $galerieId, string $photoId): void;
    public function publierGalerie(string $galerieId): void;
    public function depublierGalerie(string $galerieId): void;
}