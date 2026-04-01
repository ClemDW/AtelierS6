<?php

namespace storage\core\dto;

use Psr\Http\Message\StreamInterface;

class InputPhotoDTO
{
    public readonly string $photoId;
    public readonly StreamInterface $content;
    public readonly string $mimeType;

    public function __construct(string $photoId, StreamInterface $content, string $mimeType)
    {
        $this->photoId = $photoId;
        $this->content = $content;
        $this->mimeType = $mimeType;
    }
}
