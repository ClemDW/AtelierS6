<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\dto;

class AuthDTO
{
    public UserDTO $profile;
    public string $accessToken;
    public string $refreshToken;

    public function __construct(UserDTO $profile, string $accessToken, string $refreshToken)
    {
        $this->profile = $profile;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function toArray(): array
    {
        return [
            'profile' => $this->profile->toArray(),
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
        ];
    }
}
