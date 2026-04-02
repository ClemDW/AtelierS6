<?php
declare(strict_types=1);

namespace photopro\core\domain\exceptions;

class PhotoNotFoundException extends \RuntimeException
{
    public function __construct(string $photoId, string $galerieId)
    {
        parent::__construct("Photo $photoId introuvable dans la galerie $galerieId");
    }
}
