<?php
declare(strict_types=1);

namespace photopro\api\provider;

use photopro\core\application\ports\api\dto\CredentialsDTO;
use photopro\core\application\ports\api\dto\SignupDTO;
use photopro\core\application\ports\api\dto\AuthDTO;
use photopro\core\application\ports\api\dto\UserDTO;
use photopro\api\provider\exceptions\AuthProviderInvalidCredentialsException;
use photopro\api\provider\exceptions\AuthProviderExpiredAccessTokenException;
use photopro\api\provider\exceptions\AuthProviderInvalidAccessTokenException;

/**
 * Interface AuthProviderInterface
 * Définit les méthodes pour l'authentification JWT
 */
interface AuthProviderInterface
{
    /**
     * Enregistre un nouvel utilisateur
     * @param SignupDTO $credentials Email, mot de passe et nom
     * @return UserDTO Le profil créé
     */
    public function signup(SignupDTO $credentials): UserDTO;

    /**
     * Authentifie un utilisateur avec ses credentials
     * @param CredentialsDTO $credentials Email et mot de passe
     * @return AuthDTO Le profil + les tokens JWT
     * @throws AuthProviderInvalidCredentialsException Si credentials invalides
     */
    public function signin(CredentialsDTO $credentials): AuthDTO;

    /**
     * Récupère le profil utilisateur depuis un access token
     * @param string $accessToken Le token JWT
     * @return UserDTO Le profil de l'utilisateur authentifié
     * @throws AuthProviderExpiredAccessTokenException Si le token a expiré
     * @throws AuthProviderInvalidAccessTokenException Si le token est invalide
     */
    public function getSignedInUser(string $accessToken): UserDTO;

    /**
     * Régénère un access token à partir d'un refresh token valide
     * @param string $refreshToken Le refresh token JWT
     * @return AuthDTO Le profil + nouveaux tokens JWT
     * @throws AuthProviderExpiredAccessTokenException Si le refresh token a expiré
     * @throws AuthProviderInvalidAccessTokenException Si le refresh token est invalide
     */
    public function refresh(string $refreshToken): AuthDTO;
}
