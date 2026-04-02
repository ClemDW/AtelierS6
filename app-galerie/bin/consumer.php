<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Configuration connexion BDD Galerie (PostgreSQL)
// En environnement docker, on récupère via getenv. Adaptez si vous utilisez un .ini ou .env spécifique
$dbHost = getenv('DB_HOST') ?: 'galerie.db';
$dbName = getenv('POSTGRES_DB') ?: 'galeriedb';
$dbUser = getenv('POSTGRES_USER') ?: 'admin';
$dbPass = getenv('POSTGRES_PASSWORD') ?: 'admin';

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[Consumer Galerie] Connecté à la base de données PostgreSQL.\n";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage() . "\n");
}

// Configuration RabbitMQ
$rabbitHost = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$rabbitPort = (int)(getenv('RABBITMQ_PORT') ?: 5672);
$rabbitUser = getenv('RABBITMQ_USER') ?: 'photopro';
$rabbitPass = getenv('RABBITMQ_PASS') ?: 'photopro';

$exchangeName = 'photopro.events';
$queueName = 'sync_photographe_queue'; // La file d'attente spécifique pour la galerie
$routingKey = 'user.registered';

try {
    $connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
    $channel = $connection->channel();
    echo "[Consumer Galerie] Connecté à RabbitMQ.\n";

    // On s'assure que l'exchange existe
    $channel->exchange_declare($exchangeName, 'topic', false, true, false);

    // Déclaration de la file
    $channel->queue_declare($queueName, false, true, false, false);

    // Lier la file à l'exchange pour tout ce qui concerne les inscriptions
    $channel->queue_bind($queueName, $exchangeName, $routingKey);

    echo "[Consumer Galerie] En attente de nouveaux photographes. Pour quitter, pressez CTRL+C\n";

    $callback = function (AMQPMessage $message) use ($pdo) {
        $body = json_decode($message->getBody(), true);
        echo "=====================================\n";
        echo "[x] Reçu: ", $message->getBody(), "\n";

        if (isset($body['event_type']) && $body['event_type'] === 'user.registered' && isset($body['payload'])) {
            $data = $body['payload'];

            try {
                // Insertion dans la table photographe de db_galery
                $stmt = $pdo->prepare(
                    "INSERT INTO photographe (id, nom, pseudo, email_contact) 
                     VALUES (:id, :nom, :pseudo, :email)"
                );

                $stmt->execute([
                    ':id' => $data['id'],
                    ':nom' => $data['name'],
                    ':pseudo' => $data['pseudo'],
                    ':email' => $data['email']
                ]);

                echo "[OK] Photographe {$data['pseudo']} inséré en base avec succès !\n";
                // Validation du message (Ack)
                $message->ack();
            } catch (PDOException $e) {
                // Si l'utilisateur existe déjà (relance du script etc), on peut l'ignorer
                if ($e->getCode() == 23505) { // 23505 = unique_violation dans Postgres
                    echo "[INFO] Le photographe {$data['pseudo']} existe déjà en BDD.\n";
                    $message->ack();
                } else {
                    echo "[ERREUR SQL] : " . $e->getMessage() . "\n";
                    // On ne fait pas un ack() automatique si on veut que le message soit retry plus tard 
                    // Mais pour la robustesse, on peut le rejeter avec nack
                    $message->nack(true);
                }
            }
        } else {
            echo "[INFO] Message ignoré (format inattendu)\n";
            $message->ack();
        }
    };

    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (\Exception $e) {
    die("Erreur RabbitMQ : " . $e->getMessage() . "\n");
}
