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
            $refreshToken = $this->jwtManager->create($profile, JwtManagerInterface::REFRESH_TOKEN);

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
            // Valider le refresh token et récupérer le profil
            $profile = $this->jwtManager->validate($refreshToken);

            // Vérifier que le refresh token existe dans la BDD et est valide
            if (!$this->authRepository->isValidRefreshToken($profile->id, $refreshToken)) {
                throw new AuthProviderInvalidAccessTokenException('Refresh token is invalidated or does not exist.');
            }

            // Générer de nouveaux tokens
            $newAccessToken = $this->jwtManager->create($profile, JwtManagerInterface::ACCESS_TOKEN);
            $newRefreshToken = $this->jwtManager->create($profile, JwtManagerInterface::REFRESH_TOKEN);

            $expiresAt = new \DateTime('+30 days');
            $this->authRepository->saveRefreshToken($profile->id, $newRefreshToken, $expiresAt);

            return new AuthDTO($profile, $newAccessToken, $newRefreshToken);
        } catch (JwtManagerExpiredTokenException $e) {
            throw new AuthProviderExpiredAccessTokenException('Refresh token expired', 0, $e);
        } catch (JwtManagerInvalidTokenException $e) {
            throw new AuthProviderInvalidAccessTokenException('Invalid refresh token', 0, $e);
        }
    }
}
