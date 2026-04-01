<?php
declare(strict_types=1);

namespace photopro\api\actions;

use PDO;
use photopro\api\domain\entities\Galerie;
use photopro\core\application\ports\spi\GalerieRepositoryInterface;

class GalerieRepository implements GalerieRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getGaleriesPublic(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM galerie WHERE type_galerie = :type_galerie');
        $stmt->execute(['type_galerie' => 'public']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $galeries = [];
        foreach ($rows as $row) {
            $emails_clients_stmt = $this->pdo->prepare('SELECT email FROM invitation WHERE galerie_id = :galerie_id');
            $emails_clients_stmt->execute(['galerie_id' => $row['id']]);
            $emails_clients = $emails_clients_stmt->fetchAll(PDO::FETCH_COLUMN);
            $photosId_stmt = $this->pdo->prepare('SELECT * FROM galerie_photo WHERE galerie_id = :galerie_id');
            $photosId_stmt->execute(['galerie_id' => $row['id']]);
            $photosId = $photosId_stmt->fetchAll(PDO::FETCH_ASSOC);
            $photos = [];
            foreach ($photosId as $photoId) {
                $stmtPhoto = $this->pdo->prepare('SELECT * FROM photo WHERE id = :id');
                $stmtPhoto->execute(['id' => $photoId['photo_id']]);
                $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
                if ($photo) {
                    $photos[] = $photo;
                }
            }

            $galeries[] = new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['name'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'], 
                $row['est_publiee'], 
                $row['mode_mise_en_page'], 
                $emails_clients,
                $row['code_acces'],
                $row['url_acces_direct'],
                $photos
            );
        }
        return $galeries;
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

            $photos = [];
            foreach ($photosId as $photoId) {
                $stmtPhoto = $this->pdo->prepare('SELECT * FROM photo WHERE id = :id');
                $stmtPhoto->execute(['id' => $photoId['photo_id']]);
                $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
                if ($photo) {
                    $photos[] = $photo;
                }
            }

            return new Galerie(
                $row['id'],
                $row['photographe_id'],
                $row['type_galerie'],
                $row['name'],
                $row['description'],
                $row['date_creation'],
                $row['date_publication'],
                $row['est_publiee'],
                $row['mode_mise_en_page'],
                $emails_clients,
                $row['code_acces'],
                $row['url_acces_direct'],
                $photos
            );
        }else {
            return null;
        }
    }
}