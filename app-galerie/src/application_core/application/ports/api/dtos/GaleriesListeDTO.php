<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dtos;

class GaleriesListeDTO
{
    public string $id;
    public string $titre;
    public string $description;
    public string $date_creation;
    public string $type_galerie;
    public bool $est_publiee;
    public string $url;

    public function __construct(
        string $id,
        string $titre,
        string $description,
        string $date_creation,
        string $type_galerie,
        bool $est_publiee,
        string $url
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->date_creation = $date_creation;
        $this->type_galerie = $type_galerie;
        $this->est_publiee = $est_publiee;
        $this->url = $url;
    }
}