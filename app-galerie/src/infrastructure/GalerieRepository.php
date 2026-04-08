<?php
declare(strict_types=1);

namespace photopro\infra;

use PDO;
use photopro\core\domain\entities\Galerie;
use photopro\core\domain\entities\Photo;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;

class GalerieRepository implements GalerieRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function mapRowToPhoto(array $photosId): array
    {
        $photos = [];
        foreach ($photosId as $photoId) {
            $stmtPhoto = $this->pdo->prepare('SELECT * FROM photo WHERE id = :id');
            $stmtPhoto->execute(['id' => $photoId['photo_id']]);
            $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
            if ($photo) {
                $photos[] = new Photo(
                    $photo['id'],
                    $photo['owner_id'],
                    $photo['mime_type'],
                    (float) ($photo['taille_mo'] ?? 0),
                    $photo['nom_original'],
                    $photo['cle_s3'],
                    (string)($photo['titre'] ?? ''),
                    (string)($photo['date_upload'] ?? '')
                );
            }
        }
        return $photos;

    }

    public function getGaleriesPublic(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM galerie WHERE type_galerie = :type_galerie');
        $stmt->bindValue(':type_galerie', 'public', PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $galeries = [];
        foreach ($rows as $row) {
            $emails_clients_stmt = $this->pdo->prepare('SELECT email FROM invitation WHERE galerie_id = :galerie_id');
            $emails_clients_stmt->execute(['galerie_id' => $row['id']]);
            $emails_clients = $emails_clients_stmt->fetchAll(PDO::FETCH_COLUMN);
            $photosId_stmt = $this->pdo->prepare('SELECT * FROM galerie_photo WHERE galerie_id = :galerie_id');
            $photosId_stmt->execute(['galerie_id' => $row['id']]);
            $photosId = $photosId_stmt->fetchAll(PDO::FETCH_ASSOC);
            $photos = $this->mapRowToPhoto($photosId);
            $galeries[] = new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['titre'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'] ?? '', 
                $row['est_publiee'], 
                $row['mode_mise_en_page'], 
                $emails_clients,
                $row['code_acces'] ?? '',
                $row['url_acces'] ?? '',
                $photos,
                $row['photo_entete_id'] ?? null
            );
        }
        return $galeries;
    }

    public function getGaleriesParPhotographe(string $photographeId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM galerie WHERE photographe_id = :photographe_id');
        $stmt->execute(['photographe_id' => $photographeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $galeries = [];
        foreach ($rows as $row) {
            $emails_clients_stmt = $this->pdo->prepare('SELECT email FROM invitation WHERE galerie_id = :galerie_id');
            $emails_clients_stmt->execute(['galerie_id' => $row['id']]);
            $emails_clients = $emails_clients_stmt->fetchAll(PDO::FETCH_COLUMN);
            $photosId_stmt = $this->pdo->prepare('SELECT * FROM galerie_photo WHERE galerie_id = :galerie_id');
            $photosId_stmt->execute(['galerie_id' => $row['id']]);
            $photosId = $photosId_stmt->fetchAll(PDO::FETCH_ASSOC);
            $photos = $this->mapRowToPhoto($photosId);
            $galeries[] = new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['titre'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'] ?? '',
                $row['est_publiee'],
                $row['mode_mise_en_page'],
                $emails_clients,
                $row['code_acces'] ?? '',
                $row['url_acces'] ?? '',
                $photos,
                $row['photo_entete_id'] ?? null
            );
        }
        return $galeries;
    }

    public function getGalerieByCodeAcces(string $codeAcces): ?Galerie
    {
        $stmt = $this->pdo->prepare('SELECT * FROM galerie WHERE code_acces = :code_acces');
        $stmt->execute(['code_acces' => $codeAcces]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $emails_clients_stmt = $this->pdo->prepare('SELECT email FROM invitation WHERE galerie_id = :galerie_id');
            $emails_clients_stmt->execute(['galerie_id' => $row['id']]);
            $emails_clients = $emails_clients_stmt->fetchAll(PDO::FETCH_COLUMN);
            $photosId_stmt = $this->pdo->prepare('SELECT * FROM galerie_photo WHERE galerie_id = :galerie_id');
            $photosId_stmt->execute(['galerie_id' => $row['id']]);
            $photosId = $photosId_stmt->fetchAll(PDO::FETCH_ASSOC);
            $photos = $this->mapRowToPhoto($photosId);
            return new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['titre'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'] ?? '',
                $row['est_publiee'],
                $row['mode_mise_en_page'],
                $emails_clients,
                $row['code_acces'] ?? '',
                $row['url_acces'] ?? '',
                $photos,
                $row['photo_entete_id'] ?? null
            );
        } else {
            return null;
        }
    }


    public function creerGalerie(Galerie $galerie): Galerie
    {
        $stmt = $this->pdo->prepare(
              'INSERT INTO galerie (id, photographe_id, type_galerie, titre, description, date_creation, date_publication, est_publiee, mode_mise_en_page, code_acces, url_acces, photo_entete_id)
               VALUES (:id, :photographe_id, :type_galerie, :titre, :description, :date_creation, :date_publication, :est_publiee, :mode_mise_en_page, :code_acces, :url_acces, :photo_entete_id)'
        );
        $stmt->execute([
            'id'               => $galerie->getId(),
            'photographe_id'   => $galerie->getPhotographeId(),
            'type_galerie'     => $galerie->getType(),
            'titre'            => $galerie->getTitre(),
            'description'      => $galerie->getDescription(),
            'date_creation'    => $galerie->getDateCreation(),
            'date_publication' => $galerie->getDatePublication() ?: null,
            'est_publiee'      => $galerie->isPublic() ? 1 : 0,
            'mode_mise_en_page'=> $galerie->getMiseEnPage(),
            'code_acces'       => $galerie->getCodeAcces() ?: null,
            'url_acces' => $galerie->getUrl() ?: null,
            'photo_entete_id'  => $galerie->getPhotoEnteteId(),
        ]);

        foreach ($galerie->getEmailsClients() as $email) {
            $stmtNextId = $this->pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM invitation');
            $nextId = (int) $stmtNextId->fetchColumn();
            $stmtInv = $this->pdo->prepare(
                'INSERT INTO invitation (id, galerie_id, email) VALUES (:id, :galerie_id, :email)'
            );
            $stmtInv->execute(['id' => $nextId, 'galerie_id' => $galerie->getId(), 'email' => $email]);
        }

        foreach ($galerie->getPhotos() as $photo) {
            $stmtPhoto = $this->pdo->prepare(
                'INSERT INTO galerie_photo (galerie_id, photo_id) VALUES (:galerie_id, :photo_id)'
            );
            $stmtPhoto->execute([
                'galerie_id' => $galerie->getId(),
                'photo_id'   => is_string($photo) ? $photo : $photo->getId()
            ]);
        }

        return $galerie;
    }

    public function getGalerieById(string $id): ?Galerie
    {
        $stmt = $this->pdo->prepare('SELECT * FROM galerie WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            
            $emails_clients_stmt = $this->pdo->prepare('SELECT email FROM invitation WHERE galerie_id = :galerie_id');
            $emails_clients_stmt->execute(['galerie_id' => $row['id']]);
            $emails_clients = $emails_clients_stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmtIdPhotos = $this->pdo->prepare('SELECT * FROM galerie_photo WHERE galerie_id = :galerie_id');
            $stmtIdPhotos->execute(['galerie_id' => $row['id']]);
            $photosId = $stmtIdPhotos->fetchAll(PDO::FETCH_ASSOC);
            $photos = $this->mapRowToPhoto($photosId);
            
            return new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['titre'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'] ?? '',
                $row['est_publiee'],
                $row['mode_mise_en_page'],
                $emails_clients,
                $row['code_acces'] ?? '',
                $row['url_acces'] ?? '',
                $photos,
                $row['photo_entete_id'] ?? null
            );
        }else {
            return null;
        }
    }

    public function ajouterPhotoGalerie(string $galerieId, string $photoId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO galerie_photo (galerie_id, photo_id) VALUES (:galerie_id, :photo_id)');
        $stmt->execute(['galerie_id' => $galerieId, 'photo_id' => $photoId]);
    }

    public function supprimerPhotoGalerie(string $galerieId, string $photoId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM galerie_photo WHERE galerie_id = :galerie_id AND photo_id = :photo_id');
        $stmt->execute(['galerie_id' => $galerieId, 'photo_id' => $photoId]);
        return $stmt->rowCount() > 0;
    }

    public function publierGalerie(string $galerieId): void
    {
        $stmt = $this->pdo->prepare('UPDATE galerie SET est_publiee = TRUE, date_publication = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $galerieId]);
    }

    public function depublierGalerie(string $galerieId): void
    {
        $stmt = $this->pdo->prepare('UPDATE galerie SET est_publiee = FALSE, date_publication = NULL WHERE id = :id');
        $stmt->execute(['id' => $galerieId]);
    }

    public function ajouterEmailClient(string $galerieId, string $email): void
    {
        $stmtNextId = $this->pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM invitation');
        $nextId = (int) $stmtNextId->fetchColumn();

        $stmt = $this->pdo->prepare(
            'INSERT INTO invitation (id, galerie_id, email) VALUES (:id, :galerie_id, :email)
             ON CONFLICT (galerie_id, email) DO NOTHING'
        );
        $stmt->execute([
            'id' => $nextId,
            'galerie_id' => $galerieId,
            'email' => strtolower(trim($email)),
        ]);
    }

    public function definirPhotoEntete(string $galerieId, ?string $photoId): void
    {
        $stmt = $this->pdo->prepare('UPDATE galerie SET photo_entete_id = :photo_entete_id WHERE id = :id');
        $stmt->execute([
            'id' => $galerieId,
            'photo_entete_id' => $photoId,
        ]);
    }

    public function modifierInfosGalerie(string $galerieId, string $titre, string $description): void
    {
        $stmt = $this->pdo->prepare('UPDATE galerie SET titre = :titre, description = :description WHERE id = :id');
        $stmt->execute([
            'id' => $galerieId,
            'titre' => $titre,
            'description' => $description,
        ]);
    }

    public function modifierMiseEnPage(string $galerieId, string $miseEnPage): void
    {
        $stmt = $this->pdo->prepare('UPDATE galerie SET mode_mise_en_page = :mode WHERE id = :id');
        $stmt->execute(['mode' => $miseEnPage, 'id' => $galerieId]);
    }

    public function supprimerGalerie(string $id): void
    {
        // Suppression manuelle des dépendances car pas de ON DELETE CASCADE
        $this->pdo->prepare('DELETE FROM invitation WHERE galerie_id = :id')->execute(['id' => $id]);
        $this->pdo->prepare('DELETE FROM galerie_photo WHERE galerie_id = :id')->execute(['id' => $id]);
        
        // Suppression de la galerie
        $stmt = $this->pdo->prepare('DELETE FROM galerie WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}