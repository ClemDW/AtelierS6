<?php

namespace storage\core\dto;

class OutputPhotoDTO
{
    public readonly string $key;
    public readonly string $url;

    public function __construct(string $key, string $url)
    {
        $this->key = $key;
        $this->url = $url;
    }
}
