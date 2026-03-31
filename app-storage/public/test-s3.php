<?php
require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 1. Initialiser le client avec les variables d'environnement du storage.env
$s3Client = new S3Client([
    'version'                 => 'latest',
    'region'                  => 'us-east-1', // Une région par défaut
    'endpoint'                => 'http://S3:8333',
    'use_path_style_endpoint' => true,
    'credentials'             => [
        'key'    => getenv('AWS_ACCESS_KEY_ID') ?: 'ABCDEF', 
        'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '123456', 
    ],
]);

$bucket = 'photopro';

try {
    echo "<h1>Test de Connexion SeaweedFS S3</h1>";

    // 2. Génération d'une clé unique pour la photo (ex: UUID ou hash unique)
    // Cette clé sera celle utilisée dans S3 et sauvegardée plus tard dans votre base de données.
    $extension = 'jpg'; // Normalement, on la récupère depuis le fichier uploadé ($_FILES)
    $uniqueKey = bin2hex(random_bytes(16)) . '.' . $extension; // ex: 8a7b6c5d4e3f2a1b0c9d8e7f6a5b4c3d.jpg
    // Une autre méthode très courante est d'utiliser ramsey/uuid si vous l'avez installé :
    // $uniqueKey = \Ramsey\Uuid\Uuid::uuid4()->toString() . '.' . $extension;

    echo "Génération de la clé unique : <strong>$uniqueKey</strong><br>";
    echo "Upload en cours de la 'photo' vers S3...<br>";
    
    // 3. Upload du fichier vers S3
    $result = $s3Client->putObject([
        'Bucket' => $bucket,
        'Key'    => $uniqueKey,
        'Body'   => '... contenu binaire de la photo simulé ...', // En vrai: fopen($_FILES['photo']['tmp_name'], 'rb')
        'ContentType' => 'image/jpeg'
    ]);
    
    echo "✅ Fichier uploadé avec succès sous la clé : <strong>$uniqueKey</strong><br>";
    echo "<em>(C'est cette clé '$uniqueKey' que vous insérerez dans votre base de données 'galeriedb' ou 'userdb')</em><br><br>";

    // 4. Lire le contenu du bucket pour confirmer
    echo "<h3>Fichiers présents dans le bucket '$bucket' :</h3>";
    $objects = $s3Client->listObjectsV2([
        'Bucket' => $bucket
    ]);

    if (!empty($objects['Contents'])) {
        echo "<ul>";
        foreach ($objects['Contents'] as $object) {
            echo "<li>" . htmlspecialchars($object['Key']) . " (Taille: " . $object['Size'] . " octets)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Le bucket est vide.</p>";
    }

} catch (AwsException $e) {
    echo "<p style='color:red;'>🛑 Erreur AWS S3 : " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>🛑 Erreur Générale : " . $e->getMessage() . "</p>";
}
