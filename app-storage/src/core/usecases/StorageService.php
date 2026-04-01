<?php

namespace storage\core\usecases;

use storage\core\dto\InputPhotoDTO;
use storage\core\dto\OutputPhotoDTO;
use AWS\S3\S3Client;
use AWS\S3\Exception\S3Exception;
use Psr\Http\Message\StreamInterface;
use PDO;

class StorageService
{
    private S3Client $s3_internal_client;
    private S3Client $s3_external_client;
    private string $bucket;
    private PDO $db;

    public function __construct(S3Client $client, S3Client $s3_external_client, string $bucket, PDO $db) {
        $this->s3_internal_client = $client;
        $this->s3_external_client = $s3_external_client;
        $this->bucket = $bucket;
        $this->db = $db;
    }

    public function storePhoto(
        string $owner_id,
        StreamInterface $content,
        string $mime_type,
        float $taille_mo,
        string $nom_original,
        string $titre
    ): string {
        $uuid = $this->generateUuid();
        $extension = $this->mimeToExtension($mime_type);
        $key = sprintf('users/%s/%s.%s', $owner_id, $uuid, $extension);

        try {
            // 1. Stockage dans S3
            $this->s3_internal_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $content,
                'ContentType' => $mime_type,
                'Metadata' => [
                    'date' => date('d/m/Y H:i:s'),
                ]
            ]);

            // 2. Enregistrement en Base de données (PDO PostgreSQL)
            $stmt = $this->db->prepare("
                INSERT INTO photo (owner_id, mime_type, taille_mo, nom_original, cle_s3, titre) 
                VALUES (:owner_id, :mime_type, :taille_mo, :nom_original, :cle_s3, :titre)
                RETURNING id
            ");
            
            $stmt->execute([
                ':owner_id' => $owner_id,
                ':mime_type' => $mime_type,
                ':taille_mo' => $taille_mo,
                ':nom_original' => $nom_original,
                ':cle_s3' => $key,
                ':titre' => $titre
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id'];

        } catch (S3Exception $e) {
            throw new \Exception('Erreur S3 lors du stockage : ' . $e->getMessage(), 0, $e);
        } catch (\PDOException $e) {
            throw new \Exception('Erreur BDD lors de l\'enregistrement : ' . $e->getMessage(), 0, $e);
        }
    }

    public function getPhotoStreamAndMimeType(string $photo_id): array {
        try {
            // 1. Récupérer la clé S3 et MimeType en BDD
            $stmt = $this->db->prepare("SELECT cle_s3, mime_type FROM photo WHERE id = :id");
            $stmt->execute([':id' => $photo_id]);
            $photoInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$photoInfo) {
                throw new \Exception("Photo non trouvée dans la base de données.");
            }

            $key = $photoInfo['cle_s3'];
            $mime_type = $photoInfo['mime_type'];

            // 2. Récupérer le flux depuis S3
            $result = $this->s3_internal_client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key
            ]);

            return [
                'stream' => $result['Body'],
                'mime_type' => $mime_type
            ];
            
        } catch (S3Exception $e) {
            throw new \Exception('Erreur S3 lors de la récupération : ' . $e->getMessage());
        }
    }

    public function store(InputPhotoDTO $dto): OutputPhotoDTO {
        $extension = $this->mimeToExtension($dto->mimeType);
        $key = sprintf('photos/%s.%s', $dto->photoId, $extension);
        
        try {
            $this->s3_internal_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $dto->content,
                'ContentType' => $dto->mimeType
            ]);
            
            $url = $this->getPresignedUrl($key);
            
            return new OutputPhotoDTO($key, $url);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            throw new StorageServiceException('Erreur S3 lors du stockage : ' . $e->getMessage(), 0, $e);
        }
    }

    public function getPresignedUrl(string $key): string {
        try {
            $cmd = $this->s3_external_client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $key
            ]);

            $request = $this->s3_external_client->createPresignedRequest($cmd, '+20 minutes');
            return (string) $request->getUri();
        } catch (\Aws\S3\Exception\S3Exception $e) {
             throw new StorageServiceException('Erreur S3 lors de la création de l\'URL : ' . $e->getMessage(), 0, $e);
        }
    }

    private function mimeToExtension(string $mime): string {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'bin',
        };
    }

    private function generateUuid(): string {
        return bin2hex(random_bytes(16)); // 32 caractères hexadécimaux
    }
}
