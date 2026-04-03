<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dtos;

class CreerGalerieDTO
{
    public string $photographeId;
    public string $typeGalerie;
    public string $titre;
    public string $description;
    public bool $estPubliee;
    public string $modeMiseEnPage;
    public array $emailsClients;

    public function __construct(
        string $photographeId,
        string $typeGalerie,
        string $titre,
        string $description,
        bool $estPubliee,
        string $modeMiseEnPage,
        array $emailsClients
    ) {
        $this->photographeId = $photographeId;
        $this->typeGalerie = $typeGalerie;
        $this->titre = $titre;
        $this->description = $description;
        $this->estPubliee = $estPubliee;
        $this->modeMiseEnPage = $modeMiseEnPage;
        $this->emailsClients = $emailsClients;
    }
}