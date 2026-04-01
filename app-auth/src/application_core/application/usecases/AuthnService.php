<?php
declare(strict_types=1);

namespace photopro\core\application\usecases;

use photopro\core\application\ports\api\dto\CredentialsDTO;
use photopro\core\application\ports\api\dto\UserDTO;
use photopro\core\application\ports\api\service\AuthnServiceInterface;
use photopro\core\application\ports\api\service\AuthenticationFailedException;
use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use photopro\core\domain\exceptions\InvalidInputException;

class AuthnService implements AuthnServiceInterface
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function byCredentials(CredentialsDTO $credentials): UserDTO
    {
        $userData = $this->authRepository->findUserByEmail($credentials->email);

        if ($userData === null) {
            throw new AuthenticationFailedException('Invalid credentials');
        }

        if (!password_verify($credentials->password, $userData['password'])) {
            throw new AuthenticationFailedException('Invalid credentials');
        }

        return new UserDTO(
            $userData['id'],  
            $userData['email']
        );
    }

    public function signup(CredentialsDTO $credentials): UserDTO
    {
        // Validation de l'email
        if (!filter_var($credentials->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidInputException('Invalid email format');
        }

        // Validation du mot de passe
        if (strlen($credentials->password) < 8) {
            throw new InvalidInputException('Password must be at least 8 characters');
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($credentials->password, PASSWORD_BCRYPT);

        if ($hashedPassword === false) {
            throw new \RuntimeException('Failed to hash password');
        }

        // Création de l'utilisateur via le repository
        $userId = $this->authRepository->createUser(
            $credentials->email,
            $hashedPassword
        );

        // Retourne le profil de l'utilisateur créé
        return new UserDTO(
            $userId,
            $credentials->email
        );
    }
}
