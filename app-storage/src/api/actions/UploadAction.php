<?php

namespace storage\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use storage\core\dto\InputPhotoDTO;
use storage\core\usecases\StorageService;
use storage\core\usecases\StorageServiceException;
use storage\infra\messaging\PhotoUploadedPublisher;

class UploadAction
{
    private StorageService $storageService;
    private PhotoUploadedPublisher $publisher;

    private const array ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];
    // Taille maximale : 10 Mo
    private const int MAX_SIZE = 10 * 1024 * 1024;

    public function __construct(StorageService $storageService, PhotoUploadedPublisher $publisher) {
        $this->storageService = $storageService;
        $this->publisher = $publisher;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {

        $photograph_id = $args['id'];
        $files     = $request->getUploadedFiles();

        // 1. Fichier présent ?
        if (empty($files['photo'])) {
            throw new HttpBadRequestException($request, 'pas de fichier présent');
        }

        /** @var UploadedFileInterface $upload */
        $upload = $files['photo'];
        $clientFileName = (string) ($upload->getClientFilename() ?? 'photo');
        $sizeBytes = (int) ($upload->getSize() ?? 0);
        $sizeMo = round($sizeBytes / 1024 / 1024, 2);
        $parsedBody = $request->getParsedBody();
        $title = null;
        if (is_array($parsedBody) && isset($parsedBody['titre'])) {
            $title = trim((string) $parsedBody['titre']);
            if ($title === '') {
                $title = null;
            }
        }

        // 2. Erreur d'upload PHP ?
        if ($upload->getError() !== UPLOAD_ERR_OK) {
            throw new HttpBadRequestException($request, 'erreur upload : '. $upload->getError());
        }

        // 3. Type MIME autorisé ?
        $mimeType = $upload->getClientMediaType();
        if (!in_array($mimeType, self::ALLOWED_MIME, true)) {
            throw new HttpBadRequestException($request, 'erreur upload : type prohibé : '. $mimeType);

        }

        // 4. Taille acceptable ?
        if ($upload->getSize() > self::MAX_SIZE) {
            throw new HttpBadRequestException($request, 'erreur upload : trop volumineux' . $upload->getSize());
        }

        try {
            // Création du DTO d'entrée
            $inputDTO = new InputPhotoDTO(
                (string) $photograph_id,
                $upload->getStream(),
                $mimeType,
                $sizeMo,
                $clientFileName,
                $title
            );
            
            // stockage dans le storage service via le DTO
            $outputDTO = $this->storageService->store($inputDTO);

            // 3. Publication de l'événement (Optionnelle : ne doit pas bloquer l'upload)
            try {
                $event = [
                    'event_type' => 'photo.uploaded',
                    'timestamp' => date(DATE_ATOM),
                    'photo' => [
                        'id' => $outputDTO->photoId,
                        'owner_id' => (string) $photograph_id,
                        'mime_type' => $mimeType,
                        'taille_mo' => $sizeMo,
                        'nom_original' => $clientFileName,
                        'titre' => $title,
                        'cle_s3' => $outputDTO->key,
                    ],
                ];
                $this->publisher->publish($event);
            } catch (\Exception $e) {
                // On log l'erreur mais on ne bloque pas l'utilisateur
                error_log("RabbitMQ Error: " . $e->getMessage());
            }

            $photoId = $outputDTO->photoId;
            $key = $outputDTO->key;
            $url = $outputDTO->url;
        } catch (StorageServiceException $e) {
            throw new HttpInternalServerErrorException($request, 'erreur stockage : '. $e->getMessage());
        }

        $response->getBody()->write(json_encode([
            'photo_id' => $photoId,
            'key' => $key,
            'url' => $url,
            'queued' => true,
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);

    }

}
