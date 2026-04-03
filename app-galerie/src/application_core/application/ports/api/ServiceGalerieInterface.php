<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;
use photopro\core\application\ports\api\dtos\CreerGalerieDTO;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use photopro\core\domain\exceptions\PhotoNotFoundException;

interface ServiceGalerieInterface
{
    public function getGaleriesPublic(): array;
    public function getGaleriesParPhotographe(string $photographeId): array;
    /** @throws GalerieNotFoundException */
    public function getGalerieAffiche(string $id): GalerieAfficheDTO;
    public function creerGalerie(CreerGalerieDTO $dto): GalerieAfficheDTO;
    public function ajouterPhoto(string $galerieId, string $photoId): void;
    public function supprimerPhoto(string $galerieId, string $photoId): void;
    public function publierGalerie(string $galerieId): void;
    public function depublierGalerie(string $galerieId): void;
    /** @throws GalerieNotFoundException */
    public function modifierMiseEnPage(string $galerieId, string $miseEnPage): void;
}