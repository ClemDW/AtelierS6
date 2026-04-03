<?php
declare(strict_types=1);

namespace photopro\core\domain\entities\auth;

final class User
{
    private string $id;
    private string $email;
    private string $password;

    public function __construct(
        string $id,
        string $email,
        string $password
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
        ];
    }

    public static function fromArray(array $data): User
    {
        return new User(
            (string)$data['id'],
            (string)$data['email'],
            (string)$data['password']
        );
    }
}