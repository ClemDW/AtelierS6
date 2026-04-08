<?php
declare(strict_types=1);

namespace photopro\core\application\ports\spi;

interface GalerieEventPublisherInterface
{
    /**
     * Publie un événement galerie vers RabbitMQ.
     *
     * @param array<string, mixed> $payload
     */
    public function publish(array $payload): void;
}
