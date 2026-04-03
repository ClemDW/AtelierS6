<?php

declare(strict_types=1);

namespace photopro\core\application\ports\spi;

interface EventPublisherInterface
{
    public function publish(string $eventName, array $data): void;
}
