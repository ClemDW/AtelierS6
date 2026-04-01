<?php
declare(strict_types=1);

namespace photopro\api\domain\entities;

use Respect\Validation\Rules\Date;

class Galerie
{
    private string $id;
    private string $photographeId;
    private string $type;
    private string $titre;
    private string $description;
    private string $dateCreation;
    private string $datePublication;
    private bool $isPublic;
    private string $mise_en_page;
    private array $email_clients;
    private string $code_acces;
    private string $url;
    private array $photos;

    public function __construct(
        string $id,
        string $photographeId,
        string $type,
        string $titre,
        string $description,
        string $dateCreation,
        string $datePublication,
        bool $isPublic,
        string $mise_en_page,
        array $email_clients,
        string $code_acces,
        string $url,
        array $photos
    ) {
        $this->id = $id;
        $this->photographeId = $photographeId;
        $this->type = $type;
        $this->titre = $titre;
        $this->description = $description;
        $this->dateCreation = $dateCreation;
        $this->datePublication = $datePublication;
        $this->isPublic = $isPublic;
        $this->mise_en_page = $mise_en_page;
        $this->email_clients = $email_clients;
        $this->code_acces = $code_acces;
        $this->url = $url;
        $this->photos = $photos;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPhotographeId(): string
    {
        return $this->photographeId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDateCreation(): string
    {
        return $this->dateCreation;
    }

    public function getDatePublication(): string
    {
        return $this->datePublication;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function getMiseEnPage(): string
    {
        return $this->mise_en_page;
    }

    public function getEmailClients(): array
    {
        return $this->email_clients;
    }

    public function getCodeAcces(): string
    {
        return $this->code_acces;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

}