<?php

namespace storage\core\dto;

use Psr\Http\Message\StreamInterface;

class InputPhotoDTO
{
    public readonly string $ownerId;
    public readonly StreamInterface $content;
    public readonly string $mimeType;
    public readonly float $tailleMo;
    public readonly string $nomOriginal;
    public readonly ?string $titre;

    public function __construct(
        string $ownerId,
        StreamInterface $content,
        string $mimeType,
        float $tailleMo,
        string $nomOriginal,
        ?string $titre
    )
    {
        $this->ownerId = $ownerId;
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->tailleMo = $tailleMo;
        $this->nomOriginal = $nomOriginal;
        $this->titre = $titre;
    }
}
