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

// Récupérer les paramètres
$count = isset($_GET['count']) ? max(1, min((int)$_GET['count'], 100)) : 10;
$photosDir = '/var/data/data/cats';

echo "🛠️ Début du test d'upload bulk en base de données et sur S3...\n";
echo "📁 Répertoire: $photosDir\n";
echo "🔢 Photos à uploader: $count\n";
echo "=" . str_repeat("=", 70) . "\n\n";

try {
    // 2. Vérifier que le répertoire existe
    if (!is_dir($photosDir)) {
        die("❌ Le répertoire des photos '$photosDir' n'existe pas.\n");
    }

    // 3. Récupérer les fichiers
    $files = array_filter(
        scandir($photosDir),
        fn($f) => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f)
    );
    
    if (empty($files)) {
        die("❌ Aucune photo trouvée dans $photosDir\n");
    }

    $files = array_slice($files, 0, $count);
    
    // UUID aléatoire pour le propriétaire
    $ownerId = Uuid::uuid4()->toString();
    $mimeType = 'image/jpeg';
    
    echo "📦 Données préparées:\n";
    echo "  - Owner ID: $ownerId\n";
    echo "  - Photos trouvées: " . count($files) . "\n";
    echo "=" . str_repeat("=", 70) . "\n\n";

    $success = 0;
    $errors = 0;
    $startTime = microtime(true);
    $photoIds = [];

    foreach ($files as $index => $filename) {
        $filePath = $photosDir . '/' . $filename;
        
        try {
            $resource = fopen($filePath, 'r');
            if ($resource === false) {
                throw new Exception("Impossible d'ouvrir le fichier");
            }

            $stream = new Stream($resource);
            $tailleMo = filesize($filePath) / (1024 * 1024);
            $titre = "Photo de test #" . ($index + 1);

            $photoId = $storageService->storePhoto(
                $ownerId,
                $stream,
                $mimeType,
                $tailleMo,
                $filename,
                $titre
            );

            $photoIds[] = $photoId;
            $success++;
            
            $icon = ($index + 1) % 5 === 0 ? '🎯' : '✅';
            echo "$icon [" . str_pad((string)($index + 1), 3, ' ', STR_PAD_LEFT) . "/" . str_pad((string)count($files), 3, ' ', STR_PAD_LEFT) . "] $filename → $photoId\n";
            
            fclose($resource);

        } catch (\Exception $e) {
            $errors++;
            echo "❌ [" . str_pad((string)($index + 1), 3, ' ', STR_PAD_LEFT) . "/" . str_pad((string)count($files), 3, ' ', STR_PAD_LEFT) . "] $filename → Erreur: " . $e->getMessage() . "\n";
        }
    }

    $elapsed = microtime(true) - $startTime;
    
    echo "\n" . "=" . str_repeat("=", 70) . "\n";
    echo "📊 RÉSUMÉ:\n";
    echo "  ✅ Réussis: $success\n";
    echo "  ❌ Erreurs: $errors\n";
    echo "  ⏱️  Temps: " . number_format($elapsed, 2) . "s\n";
    if ($success > 0) {
        echo "  ⚡ Vitesse: " . number_format($success / $elapsed, 1) . " photos/sec\n";
    }
    echo "\n";
    

} catch (\Exception $e) {
    echo "❌ ERREUR LORS DU TEST :\n";
    echo $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Causée par : " . $e->getPrevious()->getMessage() . "\n";
    }
}
