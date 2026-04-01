<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use storage\core\usecases\StorageService;
use Slim\Psr7\Stream;
use Ramsey\Uuid\Uuid;

// 1. Initialisation de l'environnement et du conteneur (identique à index.php)
if (file_exists(__DIR__ . '/../../env/storage.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env', 'storage.env');
    $dotenv->load();
}

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $builder->build();

/** @var StorageService $storageService */
$storageService = $container->get(StorageService::class);

echo "🛠️ Début du test d'upload en base de données et sur S3...\n";

try {
    // 2. Préparation des données de test
    $dummyFilePath = __DIR__ . '/image.png';
    if (!file_exists($dummyFilePath)) {
        die("❌ Fichier de test dummy.jpg introuvable.\n");
    }

    $resource = fopen($dummyFilePath, 'r');
    if ($resource === false) {
        die("❌ Impossible d'ouvrir dummy.jpg.\n");
    }

    // Wrap dans un Stream Slim PSR-7
    $stream = new Stream($resource);
    
    // UUID aléatoire pour le propriétaire
    $ownerId = Uuid::uuid4()->toString();
    $mimeType = 'image/jpeg';
    $tailleMo = filesize($dummyFilePath) / (1024 * 1024);
    $nomOriginal = 'image.png';
    $titre = 'Photo de test unitaire BDD+S3';

    echo "📦 Données préparées:\n";
    echo "  - Owner ID: $ownerId\n";
    echo "  - Fichier: $nomOriginal (" . number_format($tailleMo, 6) . " Mo)\n";

    // 3. Appel de la méthode storePhoto du service (qui fait S3 + BDD)
    $photoId = $storageService->storePhoto(
        $ownerId,
        $stream,
        $mimeType,
        $tailleMo,
        $nomOriginal,
        $titre
    );

    echo "✅ SUCCÈS : La photo a été stockée avec succès.\n";
    echo "🎉 L'ID de la photo en base de données est : $photoId\n";
    
    // Garde une trace pour le test de lecture
    file_put_contents(__DIR__ . '/last_test_id.txt', $photoId);
    echo "ℹ️  (L'ID a été sauvegardé dans last_test_id.txt pour tester la lecture avec test-db-retrieve.php)\n";

} catch (\Exception $e) {
    echo "❌ ERREUR LORS DU TEST :\n";
    echo $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Causée par : " . $e->getPrevious()->getMessage() . "\n";
    }
}
