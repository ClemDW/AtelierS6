<?php

namespace storage\core\usecases;

use AWS\S3\S3Client;
use AWS\S3\Exception\S3Exception;
use Psr\Http\Message\StreamInterface;


class StorageService
{
    private S3Client $s3_internal_client;
    private S3Client $s3_external_client;
    private string $bucket;


    public function __construct(S3Client $client, S3Client $s3_external_client, string $bucket) {
        $this->s3_internal_client = $client;
        $this->s3_external_client = $s3_external_client;
        $this->bucket = $bucket;
    }

    public function store(
        string $user_id,
        StreamInterface $content,
        string $mime_type
    ): string {
        // génération de la clé
        $key = sprintf('users/%s/%s.%s', $user_id, $this->generateUuid(), $this->mimeToExtension($mime_type));

        try {
            // stockage dans le bucket : on utilise le client interne
            $this->s3_internal_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $content,
                'ContentType' => $mime_type,
                'Metadata' => [
                    'date' => date('d/m/Y H:i:s'),
                ]
            ]);
        } catch (S3Exception $e) {
            throw new StorageServiceException('erreur S3 lors du stockage :'.$e->getMessage(), $e);
        }

        return $key;
    }

    public function getPresignedUrl(
        string $key,
        int $expiresInSeconds = 3600
    ): string {
        try {
            // on utilise le client externe pour obtenir une url externe
            $command = $this->s3_external_client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return (string)$this->s3_external_client
                ->createPresignedRequest($command, "+{$expiresInSeconds} seconds")
                ->getUri();
        } catch (S3Exception $e) {
            throw new StorageServiceException('erreur S3 sur URL :'.$e->getMessage());
        }
    }
    private function mimeToExtension(
        string $mime
    ): string {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'bin',
        };
    }

    private function generateUuid(): string
    {
        return bin2hex(random_bytes(16)); // 32 caractères hexadécimaux
    }
}
