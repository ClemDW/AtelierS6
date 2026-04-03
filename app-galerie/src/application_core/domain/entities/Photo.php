<?php
declare(strict_types=1);

namespace photopro\core\domain\entities;

class Photo
{
    private string $id;
    private string $ownerId;
    private string $mimeType;
    private float $tailleMo;
    private string $nomOriginal;
    private string $cleS3;
    private string $titre;
    private string $dateUpload;

    public function __construct(
        string $id,
        string $ownerId,
        string $mimeType,
        float $tailleMo,
        string $nomOriginal,
        string $cleS3,
        string $titre,
        string $dateUpload
    ) {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->mimeType = $mimeType;
        $this->tailleMo = $tailleMo;
        $this->nomOriginal = $nomOriginal;
        $this->cleS3 = $cleS3;
        $this->titre = $titre;
        $this->dateUpload = $dateUpload;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getTailleMo(): float
    {
        return $this->tailleMo;
    }

    public function getNomOriginal(): string
    {
        return $this->nomOriginal;
    }

    public function getCleS3(): string
    {
        return $this->cleS3;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDateUpload(): string
    {
        return $this->dateUpload;
    }

}