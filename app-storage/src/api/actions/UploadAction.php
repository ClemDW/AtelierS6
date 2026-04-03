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

class UploadAction
{
    private StorageService $storageService;

    private const array ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];
    // Taille maximale : 10 Mo
    private const int MAX_SIZE = 10 * 1024 * 1024;
    public function __construct(StorageService $storageService) {
        $this->storageService = $storageService;
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
            $inputDTO = new InputPhotoDTO((string)$photograph_id, $upload->getStream(), $mimeType);
            
            // stockage dans le storage service via le DTO
            $outputDTO = $this->storageService->store($inputDTO);
            
            $key = $outputDTO->key;
            $url = $outputDTO->url;
        } catch (StorageServiceException $e) {
            throw new HttpInternalServerErrorException($request, 'erreur stockage : '. $e->getMessage());
        }
        $response->getBody()->write(json_encode(['key'=>$key, 'url'=>$url]));
        return $response->withStatus(201);

    }

}
