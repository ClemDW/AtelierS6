<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dtos;

class GalerieAfficheDTO
{
    public string $id;
    public string $photographeId;
    public string $typeGalerie;
    public string $titre;
    public string $description;
    public string $dateCreation;
    public string $datePublication;
    public bool $estPubliee;
    public string $modeMiseEnPage;
    public string $codeAcces;
    public string $url;
    public array $photos;
    public array $emailsClients;

    public function __construct(
        string $id,
        string $photographeId,
        string $typeGalerie,
        string $titre,
        string $description,
        string $dateCreation,
        string $datePublication,
        bool $estPubliee,
        string $modeMiseEnPage,
        string $codeAcces,
        string $url,
        array $photos,
        array $emailsClients
    ) {
        $this->id = $id;
        $this->photographeId = $photographeId;
        $this->typeGalerie = $typeGalerie;
        $this->titre = $titre;
        $this->description = $description;
        $this->dateCreation = $dateCreation;
        $this->datePublication = $datePublication;
        $this->estPubliee = $estPubliee;
        $this->modeMiseEnPage = $modeMiseEnPage;
        $this->codeAcces = $codeAcces;
        $this->url = $url;
        $this->photos = $photos;
        $this->emailsClients = $emailsClients;
    }
}