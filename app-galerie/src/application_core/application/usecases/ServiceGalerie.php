<?php
declare(strict_types=1);

namespace photopro\core\application\usecases;

use photopro\core\domain\entities\Galerie;
use photopro\core\domain\exceptions\GalerieNotFoundException;
use photopro\core\domain\exceptions\PhotoNotFoundException;
use photopro\core\application\ports\api\dtos\GaleriesListeDTO;
use photopro\core\application\ports\api\ServiceGalerieInterface;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;
use photopro\core\application\ports\spi\GalerieEventPublisherInterface;
use photopro\core\application\ports\api\dtos\GalerieAfficheDTO;
use photopro\core\application\ports\api\dtos\CreerGalerieDTO;
use Ramsey\Uuid\Uuid;

class ServiceGalerie implements ServiceGalerieInterface
{
    private GalerieRepositoryInterface $galerieRepository;
    private GalerieEventPublisherInterface $eventPublisher;

    public function __construct(
        GalerieRepositoryInterface $galerieRepository,
        GalerieEventPublisherInterface $eventPublisher
    ) {
        $this->galerieRepository = $galerieRepository;
        $this->eventPublisher    = $eventPublisher;
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
                $galerie->getUrl(),
                $galerie->getPhotoEnteteId()
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
                $galerie->getUrl(),
                $galerie->getPhotoEnteteId()
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
            $galerie->getEmailsClients(),
            $galerie->getPhotoEnteteId()
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
            $galerie->getEmailsClients(),
            $galerie->getPhotoEnteteId()
        );
    }

    public function getGalerieAfficheComplet(string $id): GalerieAfficheDTO
    {
        $galerie = $this->galerieRepository->getGalerieByIdComplet($id);
        if ($galerie === null) {
            throw new GalerieNotFoundException($id);
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
            $galerie->getEmailsClients(),
            $galerie->getPhotoEnteteId()
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
        $galerie = $this->getGalerieOuException($galerieId);
        $this->galerieRepository->publierGalerie($galerieId);
        $this->notifierDestinataires($galerie, 'galery.publication');
    }

    public function depublierGalerie(string $galerieId): void
    {
        $galerie = $this->getGalerieOuException($galerieId);
        $this->galerieRepository->depublierGalerie($galerieId);
        $this->notifierDestinataires($galerie, 'galery.depublication');
    }

    public function ajouterEmailClient(string $galerieId, string $email): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->ajouterEmailClient($galerieId, $email);
    }

    public function definirPhotoEntete(string $galerieId, ?string $photoId): void
    {
        $this->getGalerieOuException($galerieId);
        $this->galerieRepository->definirPhotoEntete($galerieId, $photoId);
    }

    public function modifierInfosGalerie(string $galerieId, string $titre, string $description): void
    {
        $galerie = $this->getGalerieOuException($galerieId);
        $this->galerieRepository->modifierInfosGalerie($galerieId, $titre, $description);
        // Notifier uniquement si la galerie est déjà publiée
        if ($galerie->isPublic()) {
            $this->notifierDestinataires($galerie, 'galery.modification', $titre);
        }
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
            $dto->photos,
            null
        );

        $galerieCreee = $this->galerieRepository->creerGalerie($galerie);

        if ($dto->estPubliee) {
            $this->notifierDestinataires($galerieCreee, 'galery.publication');
        }

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
            $galerieCreee->getEmailsClients(),
            $galerieCreee->getPhotoEnteteId()
        );
    }

    /**
     * Publie un événement RabbitMQ vers chaque destinataire (photographe + clients).
     *
     * @param string|null $titreOverride Titre à utiliser à la place de celui de la galerie (modification en cours)
     */
    private function notifierDestinataires(Galerie $galerie, string $eventType, ?string $titreOverride = null): void
    {
        $titre = $titreOverride ?? $galerie->getTitre();
        $timestamp = (new \DateTime())->format('c');
        $galerieData = [
            'name'        => $titre,
            'url'         => $galerie->getUrl() ?: ('/galeries/' . $galerie->getId()),
            'code_acces'  => $galerie->getCodeAcces(),
        ];

        $emailsDejaNotifies = [];

        // Notification au photographe
        $photographe = $this->galerieRepository->getPhotographeById($galerie->getPhotographeId());
        if ($photographe !== null && !empty($photographe['email_contact'])) {
            $emailPhotographe = strtolower(trim($photographe['email_contact']));
            $this->publierMessage($emailPhotographe, $photographe['nom'], '', $galerieData, $eventType, $timestamp);
            $emailsDejaNotifies[] = $emailPhotographe;
        }

        // Notification à chaque client (en évitant les doublons)
        foreach ($galerie->getEmailsClients() as $emailClient) {
            $emailNormalise = strtolower(trim($emailClient));
            if (in_array($emailNormalise, $emailsDejaNotifies, true)) {
                continue;
            }
            $this->publierMessage($emailNormalise, '', '', $galerieData, $eventType, $timestamp);
            $emailsDejaNotifies[] = $emailNormalise;
        }
    }

    private function publierMessage(
        string $email,
        string $nom,
        string $prenom,
        array  $galerieData,
        string $eventType,
        string $timestamp
    ): void {
        try {
            $this->eventPublisher->publish([
                'event_type'  => $eventType,
                'timestamp'   => $timestamp,
                'destinataire' => [
                    'email'  => $email,
                    'nom'    => $nom,
                    'prenom' => $prenom,
                ],
                'galery' => $galerieData,
            ]);
        } catch (\Throwable $e) {
            // On log l'erreur sans bloquer la réponse HTTP
            error_log('[GalerieEventPublisher] Erreur publication RabbitMQ : ' . $e->getMessage());
        }
    }
}
