<?php
declare(strict_types=1);

namespace photopro\core\application\ports\api\service;

class AuthenticationFailedException extends \Exception
{
    public function __construct(string $message = "Authentication failed", int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
