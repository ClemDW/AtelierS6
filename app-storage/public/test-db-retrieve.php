<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use storage\core\usecases\StorageService;

// 1. Initialisation de l'environnement et du conteneur
if (file_exists(__DIR__ . '/../../env/storage.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env', 'storage.env');
    $dotenv->load();
}

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $builder->build();

/** @var StorageService $storageService */
$storageService = $container->get(StorageService::class);

try {
    // 2. Lecture de l'ID généré par test-db-upload.php
    $lastTestFile = __DIR__ . '/last_test_id.txt';
    if (!file_exists($lastTestFile)) {
        header("Content-Type: text/plain");
        die("❌ Fichier de test précédent introuvable. Veuillez exécuter 'test-db-upload.php' d'abord.\n");
    }

    $photoId = trim(file_get_contents($lastTestFile));

    // 3. Appel de la méthode getPhotoStreamAndMimeType du service (S3 via key depuis la BDD)
    $photoInfo = $storageService->getPhotoStreamAndMimeType($photoId);

    $stream = $photoInfo['stream'];
    $content = $stream->getContents();

    // Affichage dans le navigateur
    header("Content-Type: " . $photoInfo['mime_type']);
    echo $content;
    exit();

} catch (\Exception $e) {
    header("Content-Type: text/plain");
    echo "❌ ERREUR LORS DU TEST :\n";
    echo $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Causée par : " . $e->getPrevious()->getMessage() . "\n";
    }
}
