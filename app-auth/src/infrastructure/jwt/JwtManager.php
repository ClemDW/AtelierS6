<?php
declare(strict_types=1);

namespace photopro\infra\jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use photopro\core\application\ports\api\dto\UserDTO;
use photopro\core\application\ports\api\jwt\JwtManagerExpiredTokenException;
use photopro\core\application\ports\api\jwt\JwtManagerInvalidTokenException;
use photopro\core\application\ports\api\jwt\JwtManagerInterface;

class JwtManager implements JwtManagerInterface
{
    private string $secret;
    private string $issuer;
    private string $algorithm;
    private int $accessTokenExpiry;
    private int $refreshTokenExpiry;

    public function __construct(
        string $secret,
        string $issuer = 'photopro.api',
        string $algorithm = 'HS256',
        int $accessTokenExpiry = 3600,
        int $refreshTokenExpiry = 2592000
    ) {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->algorithm = $algorithm;
        $this->accessTokenExpiry = $accessTokenExpiry;
        $this->refreshTokenExpiry = $refreshTokenExpiry;
    }

    public function create(UserDTO $profile, int $type): string
    {
        $now = time();
        $expiry = $type === self::ACCESS_TOKEN 
            ? $now + $this->accessTokenExpiry 
            : $now + $this->refreshTokenExpiry;

        $payload = [
            'iss' => $this->issuer,
            'iat' => $now,
            'exp' => $expiry,
            'sub' => $profile->id,
            'email' => $profile->email
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validate(string $token): UserDTO
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            
            return new UserDTO(
                $decoded->sub,
                $decoded->email
            );
        } catch (ExpiredException $e) {
            throw new JwtManagerExpiredTokenException('Token has expired', 0, $e);
        } catch (\Exception $e) {
            throw new JwtManagerInvalidTokenException('Invalid token: ' . $e->getMessage(), 0, $e);
        }
    }
}
