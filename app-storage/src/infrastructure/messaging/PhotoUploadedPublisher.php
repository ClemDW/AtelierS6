<?php

declare(strict_types=1);

namespace storage\infra\messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class PhotoUploadedPublisher
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $pass,
        private readonly string $exchange,
        private readonly string $routingKey
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function publish(array $payload): void
    {
        $connection = null;
        $channel = null;

        try {
            $connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->pass
            );
            $channel = $connection->channel();

            $channel->exchange_declare($this->exchange, 'topic', false, true, false);

            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($body === false) {
                throw new \RuntimeException('JSON invalide pour publication evenement photo');
            }

            $message = new AMQPMessage($body, [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($message, $this->exchange, $this->routingKey);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Publication RabbitMQ impossible: ' . $e->getMessage(), 0, $e);
        } finally {
            if ($channel !== null) {
                $channel->close();
            }
            if ($connection !== null) {
                $connection->close();
            }
        }
    }
}
