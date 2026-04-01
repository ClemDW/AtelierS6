<?php
declare(strict_types=1);

namespace photopro\core\application\usecases;

use photopro\core\domain\entities\Galerie;
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
        $this->galerieRepository->ajouterPhotoGalerie($galerieId, $photoId);
    }

    public function supprimerPhoto(string $galerieId, string $photoId): void
    {
        $this->galerieRepository->supprimerPhotoGalerie($galerieId, $photoId);
    }

    public function publierGalerie(string $galerieId): void
    {
        $this->galerieRepository->publierGalerie($galerieId);
    }

    public function depublierGalerie(string $galerieId): void
    {
        $this->galerieRepository->depublierGalerie($galerieId);
    }
    
    public function creerGalerie(CreerGalerieDTO $dto): GalerieAfficheDTO
    {
        $id = Uuid::uuid4()->toString();
        $dateCreation = (new \DateTime())->format('Y-m-d H:i:s');
        $datePublication = $dto->estPubliee ? $dateCreation : '';
        $codeAcces = $dto->typeGalerie !== 'public' ? bin2hex(random_bytes(4)) : '';
        $url = $dto->typeGalerie === 'public' ? '/galeries/' . $id : '';

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