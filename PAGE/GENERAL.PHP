<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'votre_base_de_donnees';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupération des publications
$sql = "SELECT auteur, contenu, image_url, video_url, date_publication FROM publications ORDER BY date_publication DESC";
$publications = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace de Publication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Ajoutez ici le style CSS déjà fourni */
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="#" class="logo">Espace de Publication</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="post-creator">
            <form method="POST" action="publier.php" enctype="multipart/form-data">
                <textarea class="post-input" name="contenu" placeholder="Que voulez-vous partager ?"></textarea>
                <div class="media-preview">
                    <img id="imagePreview" src="" alt="Preview">
                    <video id="videoPreview" controls style="display: none;">
                        Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                </div>
                <div class="post-actions">
                    <div class="media-buttons">
                        <button type="button" class="btn btn-secondary" id="imageButton">
                            <i class="fas fa-image"></i> Image
                        </button>
                        <button type="button" class="btn btn-secondary" id="videoButton">
                            <i class="fas fa-video"></i> Vidéo
                        </button>
                        <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                        <input type="file" id="videoInput" name="video" accept="video/*" style="display: none;">
                    </div>
                    <button type="submit" class="btn btn-primary" id="publishButton">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                </div>
            </form>
        </div>

        <div class="feed">
            <?php foreach ($publications as $publication): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="https://via.placeholder.com/40" alt="Avatar" class="post-avatar">
                        <div class="post-info">
                            <div class="post-author"><?= htmlspecialchars($publication['auteur']) ?></div>
                            <div class="post-time"><?= htmlspecialchars($publication['date_publication']) ?></div>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($publication['contenu'])) ?>
                    </div>
                    <?php if (!empty($publication['image_url'])): ?>
                        <img src="<?= htmlspecialchars($publication['image_url']) ?>" alt="Publication image" class="post-image">
                    <?php elseif (!empty($publication['video_url'])): ?>
                        <video controls class="post-video">
                            <source src="<?= htmlspecialchars($publication['video_url']) ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Ajoutez ici le script JS déjà fourni
    </script>
</body>
</html>
