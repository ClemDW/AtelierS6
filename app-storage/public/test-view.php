<?php
require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$error = null;

if (isset($_GET['key']) && !empty($_GET['key'])) {
    $key = $_GET['key'];
    
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-1',
        'endpoint' => 'http://S3:8333',
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key'    => 'ABCDEF', 
            'secret' => '123456',
        ],
    ]);

    try {
        $result = $s3Client->getObject([
            'Bucket' => 'photopro',
            'Key'    => $key
        ]);
        
        // Si l'image est trouvée, on l'affiche directement sur la page 
        // en envoyant les bons en-têtes (headers)
        header("Content-Type: " . $result['ContentType']);
        // optionnel: header("Content-Length: " . $result['ContentLength']);
        echo $result['Body'];
        
        // On arrête le script PHP ici pour ne pas afficher le HTML (sinon l'image est corrompue)
        exit;

    } catch (AwsException $e) {
        $error = "L'image demandée n'existe pas ou le serveur S3 est inaccessible. (Erreur S3: " . $e->getAwsErrorCode() . ")";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Lecture Photo (S3)</title>
    <style>body { font-family: sans-serif; margin: 2rem; }</style>
</head>
<body>
    <h1>Charger et afficher une photo depuis SeaweedFS</h1>
    
    <?php if ($error): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <hr>
    <?php endif; ?>

    <form method="GET">
        <label for="key">Entrez la clé S3 de l'image (ex: abc123def456.jpg) :</label><br><br>
        <input type="text" name="key" id="key" required style="width: 300px;"><br><br>
        <button type="submit">Afficher la photo</button>
    </form>
</body>
</html>