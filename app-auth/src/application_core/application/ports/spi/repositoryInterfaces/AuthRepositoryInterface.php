<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi\repositoryInterfaces;

use photopro\core\domain\exceptions\EmailAlreadyExistsException;

interface AuthRepositoryInterface
{
    /**
     * Récupère un utilisateur par email
     * 
     * @param string $email
     * @return array|null 
     */
    public function findUserByEmail(string $email): ?array;

    /**
     * Crée un nouvel utilisateur
     * 
     * @param string $email
     * @param string $hashedPassword
     * @return string L'ID de l'utilisateur créé
     * @throws EmailAlreadyExistsException Si l'email existe déjà
     */
    public function createUser(string $email, string $hashedPassword): string;

    /**
     * Sauvegarde un refresh token en base de données pour un utilisateur
     * 
     * @param string $userId
     * @param string $refreshToken
     * @param \DateTime $expiresAt
     * @return void
     */
    public function saveRefreshToken(string $userId, string $refreshToken, \DateTime $expiresAt): void;

    /**
     * Valide qu'un refresh token existe bien en base pour un utilisateur
     * 
     * @param string $userId
     * @param string $refreshToken
     * @return bool
     */
    public function isValidRefreshToken(string $userId, string $refreshToken): bool;
}