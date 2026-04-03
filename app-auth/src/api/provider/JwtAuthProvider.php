<?php
declare(strict_types=1);

namespace photopro\api\provider;

use photopro\core\application\ports\api\dto\CredentialsDTO;
use photopro\core\application\ports\api\dto\SignupDTO;
use photopro\core\application\ports\api\dto\AuthDTO;
use photopro\core\application\ports\api\dto\UserDTO;
use photopro\api\provider\AuthProviderInterface;
use photopro\api\provider\exceptions\AuthProviderInvalidCredentialsException;
use photopro\api\provider\exceptions\AuthProviderExpiredAccessTokenException;
use photopro\api\provider\exceptions\AuthProviderInvalidAccessTokenException;
use photopro\core\application\ports\api\jwt\JwtManagerInterface;
use photopro\core\application\ports\api\jwt\JwtManagerExpiredTokenException;
use photopro\core\application\ports\api\jwt\JwtManagerInvalidTokenException;
use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use photopro\core\application\ports\api\service\AuthnServiceInterface;
use photopro\core\application\ports\api\service\AuthenticationFailedException;

class JwtAuthProvider implements AuthProviderInterface
{
    private AuthnServiceInterface $authnService;
    private JwtManagerInterface $jwtManager;
    private AuthRepositoryInterface $authRepository;

    public function __construct(
        AuthnServiceInterface $authnService,
        JwtManagerInterface $jwtManager,
        AuthRepositoryInterface $authRepository
    ) {
        $this->authnService = $authnService;
        $this->jwtManager = $jwtManager;
        $this->authRepository = $authRepository;
    }

    public function signup(SignupDTO $credentials): UserDTO
    {
        return $this->authnService->signup($credentials);
    }

    public function signin(CredentialsDTO $credentials): AuthDTO
    {
        try {
            $profile = $this->authnService->byCredentials($credentials);

            $accessToken = $this->jwtManager->create($profile, JwtManagerInterface::ACCESS_TOKEN);
            $refreshToken = bin2hex(random_bytes(32));

            // 30 jours
            $expiresAt = new \DateTime('+30 days');
            
            // On sauvegarde le refresh token généré dans la base
            $this->authRepository->saveRefreshToken($profile->id, $refreshToken, $expiresAt);

            return new AuthDTO($profile, $accessToken, $refreshToken);
        } catch (AuthenticationFailedException $e) {
            throw new AuthProviderInvalidCredentialsException('Invalid credentials', 0, $e);
        }
    }

    public function getSignedInUser(string $accessToken): UserDTO
    {
        try {
            return $this->jwtManager->validate($accessToken);
        } catch (JwtManagerExpiredTokenException $e) {
            throw new AuthProviderExpiredAccessTokenException('Access token expired', 0, $e);
        } catch (JwtManagerInvalidTokenException $e) {
            throw new AuthProviderInvalidAccessTokenException('Invalid access token', 0, $e);
        }
    }

    public function refresh(string $refreshToken): AuthDTO
    {
        try {
            // Vérifier que le refresh token existe dans la BDD et récupérer l'utilisateur
            $userRow = $this->authRepository->findUserByRefreshToken($refreshToken);

            if (!$userRow) {
                throw new AuthProviderInvalidAccessTokenException('Refresh token is invalid or expired.');
            }

            // Construire le UserDTO avec les infos récupérées
            $profile = new UserDTO($userRow['id'], $userRow['email']);

            // Supprimer l'ancien refresh token (Rotation de Token pour la sécurité)
            $this->authRepository->revokeRefreshToken($refreshToken);

            // Générer de nouveaux tokens
            $newAccessToken = $this->jwtManager->create($profile, JwtManagerInterface::ACCESS_TOKEN);
            $newRefreshToken = bin2hex(random_bytes(32));

            $expiresAt = new \DateTime('+30 days');
            $this->authRepository->saveRefreshToken($profile->id, $newRefreshToken, $expiresAt);

            return new AuthDTO($profile, $newAccessToken, $newRefreshToken);
        } catch (\Exception $e) {
            throw new AuthProviderInvalidAccessTokenException('Invalid refresh token', 0, $e);
        }
    }
}
