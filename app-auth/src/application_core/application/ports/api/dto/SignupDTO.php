<?php

declare(strict_types=1);

namespace photopro\core\application\ports\api\dto;

class SignupDTO
{
    public string $email;
    public string $password;
    public string $name;

    public function __construct(string $email, string $password, string $name)
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
    }
}
