<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\service;

use photopro\core\application\ports\api\dto\CredentialsDTO;
use photopro\core\application\ports\api\dto\SignupDTO;
use photopro\core\application\ports\api\dto\UserDTO;

interface AuthnServiceInterface
{
    /**
     * @param CredentialsDTO $credentials
     * @return UserDTO
     * @throws AuthenticationFailedException
     */
    public function byCredentials(CredentialsDTO $credentials): UserDTO;

    /**
     * @param SignupDTO $credentials
     * @return UserDTO
     */
    public function signup(SignupDTO $credentials): UserDTO;
}
