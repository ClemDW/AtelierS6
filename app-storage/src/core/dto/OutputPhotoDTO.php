<?php

namespace storage\core\dto;

class OutputPhotoDTO
{
    public readonly string $photoId;
    public readonly string $key;
    public readonly string $url;

    public function __construct(string $photoId, string $key, string $url)
    {
        $this->photoId = $photoId;
        $this->key = $key;
        $this->url = $url;
    }
}
