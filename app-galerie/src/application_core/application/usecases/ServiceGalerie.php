<?php
declare(strict_types=1);

namespace photopro\core\application\usecases;

use photopro\api\actions\GalerieRepository;
use photopro\api\domain\entities\Galerie;
use photopro\core\application\ports\api\dtos\GaleriesListeDTO;
use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;

class ServiceGalerie implements ServiceGalerieInterface
{
    private GalerieRepository $galerieRepository;

    public function __construct(GalerieRepository $galerieRepository)
    {
        $this->galerieRepository = $galerieRepository;
    }

    public function getGaleriesPublic(): array
    {
        $liste_galeries = $this->galerieRepository->getGaleriesPublic();
        $galeries = [];
        foreach ($liste_galeries as $galerie) {
            $galeries[] = new GaleriesListeDTO(
                $galerie->getId(),
                $galerie->getTitre(),
                $galerie->getDescription(),
                $galerie->getDateCreation(),
                $galerie->getUrl()
            );
        }
        return $galeries;
    }

    public function getGalerieAffiche(string $id): ?GalerieAfficheDTO
    {
        $galerie = $this->galerieRepository->getGalerieById($id);
        if ($galerie === null) {
            return null;
        }
        return new GalerieAfficheDTO(
            $galerie->getId(),
            $galerie->getTitre(),
            $galerie->getDescription(),
            $galerie->getDateCreation(),
            $galerie->getUrl()
        );
    }
}