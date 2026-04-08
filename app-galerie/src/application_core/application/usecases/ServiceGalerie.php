<?php
declare(strict_types=1);

namespace photopro\core\application\usecases;

use photopro\core\domain\entities\Galerie;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use photopro\core\domain\exceptions\PhotoNotFoundException;
use photopro\core\application\ports\api\dtos\GaleriesListeDTO;
use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;
use photopro\core\application\ports\api\dtos\CreerGalerieDTO;
use Ramsey\Uuid\Uuid;

class ServiceGalerie implements ServiceGalerieInterface
{
    private GalerieRepositoryInterface $galerieRepository;

    public function __construct(GalerieRepositoryInterface $galerieRepository)
    {
        $this->galerieRepository = $galerieRepository;
    }

    private function getGalerieOuException(string $galerieId): Galerie
    {
        $galerie = $this->galerieRepository->getGalerieById($galerieId);
        if ($galerie === null) {
            throw new GalerieNotFoundException($galerieId);
        }
        return $galerie;
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
                $galerie->getType(),
                $galerie->isPublic(),
                $galerie->getUrl()
            );
        }
        return $galeries;
    }

    public function getGaleriesParPhotographe(string $photographeId): array
    {
        $liste = $this->galerieRepository->getGaleriesParPhotographe($photographeId);
        $galeries = [];
        foreach ($liste as $galerie) {
            $galeries[] = new GaleriesListeDTO(
                $galerie->getId(),
                $galerie->getTitre(),
                $galerie->getDescription(),
                $galerie->getDateCreation(),
                $galerie->getType(),
                $galerie->isPublic(),
                $galerie->getUrl()
            );
        }
        return $galeries;
    }

    public function getGalerieByCodeAcces(string $codeAcces): GalerieAfficheDTO
    {
        $galerie = $this->galerieRepository->getGalerieByCodeAcces($codeAcces);
        if ($galerie === null) {
            throw new GalerieNotFoundException("Code d'accès : " . $codeAcces);
        }
        return new GalerieAfficheDTO(
            $galerie->getId(),
            $galerie->getPhotographeId(),
            $galerie->isPublic() ? 'public' : 'privée',
            $galerie->getTitre(),
            $galerie->getDescription(),
            $galerie->getDateCreation(),
            $galerie->getDatePublication(),
            $galerie->isPublic(),
            $galerie->getMiseEnPage(),
            $galerie->getCodeAcces(),
            $galerie->getUrl(),
            $galerie->getPhotos(),
            $galerie->getEmailsClients()
        );
    }

    public function getGalerieAffiche(string $id): GalerieAfficheDTO
    {
        $galerie = $this->getGalerieOuException($id);
        return new GalerieAfficheDTO(
            $galerie->getId(),
            $galerie->getPhotographeId(),
            $galerie->isPublic() ? 'public' : 'privée',
            $galerie->getTitre(),
            $galerie->getDescription(),
            $galerie->getDateCreation(),
            $galerie->getDatePublication(),
            $galerie->isPublic(),
            $galerie->getMiseEnPage(),
            $galerie->getCodeAcces(),
            $galerie->getUrl(),
            $galerie->getPhotos(),
            $galerie->getEmailsClients()
        );
    }

    public function ajouterPhoto(string $galerieId, string $photoId): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->ajouterPhotoGalerie($galerieId, $photoId);
    }

    public function supprimerPhoto(string $galerieId, string $photoId): void
    {
        $this->getGalerieOuException($galerieId);
        $supprime = $this->galerieRepository->supprimerPhotoGalerie($galerieId, $photoId);
        if (!$supprime) {
            throw new PhotoNotFoundException($photoId, $galerieId);
        }
    }

    public function publierGalerie(string $galerieId): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->publierGalerie($galerieId);
    }

    public function depublierGalerie(string $galerieId): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->depublierGalerie($galerieId);
    }

    public function modifierMiseEnPage(string $galerieId, string $miseEnPage): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->modifierMiseEnPage($galerieId, $miseEnPage);
    }

    public function supprimerGalerie(string $galerieId): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->supprimerGalerie($galerieId);
    }

    public function creerGalerie(CreerGalerieDTO $dto): GalerieAfficheDTO
    {
        $id = Uuid::uuid4()->toString();
        $dateCreation = (new \DateTime())->format('c'); // Format ISO 8601 pour JavaScript
        $datePublication = $dto->estPubliee ? $dateCreation : '';
        $codeAcces = $dto->typeGalerie !== 'public' ? bin2hex(random_bytes(4)) : '';
        $url = $dto->typeGalerie !== 'public' ? '/galeries/' . $id : '';

        $galerie = new Galerie(
            $id,
            $dto->photographeId,
            $dto->typeGalerie,
            $dto->titre,
            $dto->description,
            $dateCreation,
            $datePublication,
            $dto->estPubliee,
            $dto->modeMiseEnPage,
            $dto->emailsClients,
            $codeAcces,
            $url,
            $dto->photos
        );

        $galerieCreee = $this->galerieRepository->creerGalerie($galerie, $dto->emailsClients);

        return new GalerieAfficheDTO(
            $galerieCreee->getId(),
            $galerieCreee->getPhotographeId(),
            $galerieCreee->isPublic() ? 'public' : 'privée',
            $galerieCreee->getTitre(),
            $galerieCreee->getDescription(),
            $galerieCreee->getDateCreation(),
            $galerieCreee->getDatePublication(),
            $galerieCreee->isPublic(),
            $galerieCreee->getMiseEnPage(),
            $galerieCreee->getCodeAcces(),
            $galerieCreee->getUrl(),
            $galerieCreee->getPhotos(),
            $galerieCreee->getEmailsClients()
        );
    }
}
