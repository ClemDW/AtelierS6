<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dtos;

class GalerieAfficheDTO
{
    public string $id;
    public string $titre;
    public string $description;
    public string $dateCreation;
    public string $url;

    public function __construct(
        string $id,
        string $titre,
        string $description,
        string $dateCreation,
        string $url
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->dateCreation = $dateCreation;
        $this->url = $url;
    }
}