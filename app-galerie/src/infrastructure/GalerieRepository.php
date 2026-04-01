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
                    $photo['taille_mo'],
                    $photo['nom_original'],
                    $photo['cle_s3'],
                    $photo['titre'],
                    $photo['date_upload']
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
                $photos
            );
        }
        return $galeries;
    }

    public function creerGalerie(Galerie $galerie): Galerie
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO galerie (id, photographe_id, type_galerie, titre, description, date_creation, date_publication, est_publiee, mode_mise_en_page, code_acces, url_acces)
             VALUES (:id, :photographe_id, :type_galerie, :titre, :description, :date_creation, :date_publication, :est_publiee, :mode_mise_en_page, :code_acces, :url_acces)'
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
        ]);

        foreach ($galerie->getEmailsClients() as $email) {
            $stmtInv = $this->pdo->prepare(
                'INSERT INTO invitation (galerie_id, email) VALUES (:galerie_id, :email)'
            );
            $stmtInv->execute(['galerie_id' => $galerie->getId(), 'email' => $email]);
        }

        foreach ($galerie->getPhotos() as $photo) {
            $stmtPhoto = $this->pdo->prepare(
                'INSERT INTO galerie_photo (galerie_id, url, mime_type, taille_mo, nom_original, cle_s3, titre, date_upload) VALUES (:galerie_id, :url, :mime_type, :taille_mo, :nom_original, :cle_s3, :titre, :date_upload)'
            );
            $stmtPhoto->execute([
                'galerie_id' => $galerie->getId(),
                'url' => $photo->getUrl(),
                'mime_type' => $photo->getMimeType(),
                'taille_mo' => $photo->getTailleMo(),
                'nom_original' => $photo->getNomOriginal(),
                'cle_s3' => $photo->getCleS3(),
                'titre' => $photo->getTitre(),
                'date_upload' => $photo->getDateUpload()
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
                $photos
            );
        }else {
            return null;
        }
    }
}