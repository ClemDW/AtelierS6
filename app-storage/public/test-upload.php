<?php
require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$message = '';
$uploadedKey = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
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

        $tmpPath = $_FILES['photo']['tmp_name'];
        $originalName = $_FILES['photo']['name'];
        
        // Extraction de l'extension du fichier
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        if (empty($extension)) { $extension = 'jpg'; }
        
        // Génération d'une clé unique (ex: abc123def456.jpg)
        $uniqueKey = bin2hex(random_bytes(16)) . '.' . $extension;
        $bucket = 'photopro';

        try {
            $s3Client->putObject([
                'Bucket'      => $bucket,
                'Key'         => $uniqueKey,
                'SourceFile'  => $tmpPath,
                'ContentType' => $_FILES['photo']['type']
            ]);
            $message = "✅ Photo uploadée avec succès !";
            $uploadedKey = $uniqueKey;
        } catch (AwsException $e) {
            $message = "🛑 Erreur AWS S3 : " . $e->getMessage();
        }
    } else {
        $message = "🛑 Erreur lors de l'upload du fichier (Code: " . $_FILES['photo']['error'] . ")";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Upload Photo (S3)</title>
    <style>body { font-family: sans-serif; margin: 2rem; }</style>
</head>
<body>
    <h1>Uploader une photo vers SeaweedFS</h1>
    
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
        <?php if ($uploadedKey): ?>
            <p>Clé générée pour ce fichier : <strong style="color: blue; font-size: 1.2em;"><?= $uploadedKey ?></strong></p>
            <p><em>(Copiez cette clé pour la tester dans l'autre fichier)</em></p>
            <p><a href="test-view.php?key=<?= $uploadedKey ?>" target="_blank">Tester l'affichage brut</a></p>
        <?php endif; ?>
        <hr>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="photo">Sélectionnez une image sur votre ordinateur :</label><br><br>
        <input type="file" name="photo" id="photo" accept="image/*" required><br><br>
        <button type="submit">Uploader vers S3</button>
    </form>
</body>
</html>