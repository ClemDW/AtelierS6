<?php
declare(strict_types=1);

namespace photopro\core\domain\exceptions;

class EmailAlreadyExistsException extends \Exception
{
    public function __construct(string $message = "Cet email est déjà utilisé.", int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
