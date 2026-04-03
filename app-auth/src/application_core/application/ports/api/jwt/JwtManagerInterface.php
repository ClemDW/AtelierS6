<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\jwt;

use photopro\core\application\ports\api\dto\UserDTO;

/**
 * Interface JwtManagerInterface
 * Gère la création et validation des tokens JWT
 */
interface JwtManagerInterface
{
    public const ACCESS_TOKEN = 1;
    public const REFRESH_TOKEN = 2;

    /**
     * Crée un token JWT
     * @param UserDTO  L'utilisateur
     * @return string Le token JWT signé
     */
    public function create(UserDTO $profile, int $type): string;

    /**
     * Valide et décode un token JWT
     * @param string $token Le token à valider
     * @return UserDTO L'utilisateur extrait du token
     * @throws JwtManagerExpiredTokenException Si le token a expiré
     * @throws JwtManagerInvalidTokenException Si le token est invalide
     */
    public function validate(string $token): UserDTO;
}
