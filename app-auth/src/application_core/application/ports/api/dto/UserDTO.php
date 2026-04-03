<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dto;


final class UserDTO
{
    public string $id;
    public string $email;

    public function __construct(string $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string)$data['id'],
            (string)$data['email']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
        ];
    }
}