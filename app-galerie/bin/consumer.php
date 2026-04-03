<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$rabbitHost = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$rabbitPort = (int) (getenv('RABBITMQ_PORT') ?: '5672');
$rabbitUser = getenv('RABBITMQ_USER') ?: 'photopro';
$rabbitPass = getenv('RABBITMQ_PASS') ?: 'photopro';
$exchange = getenv('RABBITMQ_EXCHANGE') ?: 'photopro.events';
$queueName = getenv('RABBITMQ_QUEUE_GALERIE_PHOTOS') ?: 'galerie_photos';
$routingKey = getenv('RABBITMQ_ROUTING_KEY_PHOTO_UPLOADED') ?: 'photo.uploaded';

$dbHost = getenv('DB_HOST') ?: 'galerie.db';
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'galeriedb';
$dbUser = getenv('DB_USER') ?: 'admin';
$dbPass = getenv('DB_PASS') ?: 'admin';

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $dbHost, $dbPort, $dbName);

echo "[GalerieConsumer] Starting...\n";
echo "[GalerieConsumer] RabbitMQ: {$rabbitHost}:{$rabbitPort} exchange={$exchange} queue={$queueName}\n";
echo "[GalerieConsumer] DB: {$dbHost}:{$dbPort}/{$dbName}\n";

$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
$channel = $connection->channel();

$channel->exchange_declare($exchange, 'topic', false, true, false);
$channel->queue_declare($queueName, false, true, false, false);
$channel->queue_bind($queueName, $exchange, $routingKey);
$channel->basic_qos(null, 1, null);

$insertSql = <<<SQL
INSERT INTO photo (id, owner_id, mime_type, taille_mo, nom_original, cle_s3, titre)
VALUES (:id, :owner_id, :mime_type, :taille_mo, :nom_original, :cle_s3, :titre)
ON CONFLICT (id) DO UPDATE SET
    owner_id = EXCLUDED.owner_id,
    mime_type = EXCLUDED.mime_type,
    taille_mo = EXCLUDED.taille_mo,
    nom_original = EXCLUDED.nom_original,
    cle_s3 = EXCLUDED.cle_s3,
    titre = EXCLUDED.titre
SQL;

$insertStmt = $pdo->prepare($insertSql);

$callback = function (AMQPMessage $message) use ($insertStmt): void {
    echo "\n[GalerieConsumer] Message received\n";

    $payload = json_decode($message->getBody(), true);
    if (!is_array($payload)) {
        echo "[GalerieConsumer] Invalid JSON, ack and skip\n";
        $message->ack();
        return;
    }

    $eventType = (string) ($payload['event_type'] ?? '');
    if ($eventType !== 'photo.uploaded') {
        echo "[GalerieConsumer] Unsupported event '{$eventType}', ack and skip\n";
        $message->ack();
        return;
    }

    $photo = $payload['photo'] ?? null;
    if (!is_array($photo)) {
        echo "[GalerieConsumer] Missing photo payload, ack and skip\n";
        $message->ack();
        return;
    }

    try {
        $insertStmt->execute([
            ':id' => (string) ($photo['id'] ?? ''),
            ':owner_id' => (string) ($photo['owner_id'] ?? ''),
            ':mime_type' => (string) ($photo['mime_type'] ?? ''),
            ':taille_mo' => (float) ($photo['taille_mo'] ?? 0),
            ':nom_original' => (string) ($photo['nom_original'] ?? 'photo'),
            ':cle_s3' => (string) ($photo['cle_s3'] ?? ''),
            ':titre' => isset($photo['titre']) ? (string) $photo['titre'] : null,
        ]);

        echo "[GalerieConsumer] Photo persisted: id=" . (string) ($photo['id'] ?? '') . "\n";
        $message->ack();
    } catch (Throwable $e) {
        echo "[GalerieConsumer] DB error: {$e->getMessage()}\n";
        $message->nack(false, true);
    }
};

$channel->basic_consume($queueName, '', false, false, false, false, $callback);

echo "[GalerieConsumer] Waiting for messages...\n";

try {
    while ($channel->is_consuming()) {
        $channel->wait();
    }
} finally {
    $channel->close();
    $connection->close();
}
