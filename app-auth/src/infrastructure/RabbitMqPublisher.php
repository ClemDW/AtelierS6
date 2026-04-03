<?php

declare(strict_types=1);

namespace photopro\infra;

use photopro\core\application\ports\spi\EventPublisherInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqPublisher implements EventPublisherInterface
{
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $exchangeName;

    public function __construct(string $host, int $port, string $user, string $password, string $exchangeName)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->exchangeName = $exchangeName;
    }

    public function publish(string $eventName, array $data): void
    {
        try {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
            $channel = $connection->channel();

            // Déclarer l'exchange de type topic
            $channel->exchange_declare($this->exchangeName, 'topic', false, true, false);

            $messageBody = json_encode([
                'event_type' => $eventName,
                'payload' => $data
            ]);

            $message = new AMQPMessage($messageBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $channel->basic_publish($message, $this->exchangeName, $eventName);

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            // Logs de l'erreur... En production on loggerait le message.
            error_log("RabbitMQ Publish Error: " . $e->getMessage());
        }
    }
}
