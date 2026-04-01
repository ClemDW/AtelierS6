<?php
declare(strict_types=1);

namespace photopro\core\domain\exceptions;

class InvalidInputException extends \Exception
{
    public function __construct(string $message = "Données d'entrée invalides.", int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
