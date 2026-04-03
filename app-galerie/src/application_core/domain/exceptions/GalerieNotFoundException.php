<?php
declare(strict_types=1);

namespace photopro\core\domain\exceptions;

class GalerieNotFoundException extends \RuntimeException
{
    public function __construct(string $galerieId)
    {
        parent::__construct("Galerie introuvable : $galerieId");
    }
}
