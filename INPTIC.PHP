<?php
$host = 'localhost'; // Hôte de la base de données
$dbname = 'gestion_documents'; // Nom de la base de données
$username = 'root'; // Utilisateur MySQL
$password = ''; // Mot de passe MySQL

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

get_users.php

<?php
include 'db.php';

// Récupérer les utilisateurs et leurs documents
$query = "SELECT u.id AS user_id, u.name, u.phone, d.id AS doc_id, d.date, d.type, d.status, d.fileName 
          FROM users u
          LEFT JOIN documents d ON u.id = d.user_id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
?>

INDEX.PHP 

<?php
include 'db.php';

// Récupérer les utilisateurs et leurs documents
$query = "SELECT u.id AS user_id, u.name, u.phone, d.id AS doc_id, d.date, d.type, d.status, d.fileName 
          FROM users u
          LEFT JOIN documents d ON u.id = d.user_id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les données par utilisateur
$organizedUsers = [];
foreach ($users as $user) {
    $organizedUsers[$user['user_id']]['name'] = $user['name'];
    $organizedUsers[$user['user_id']]['phone'] = $user['phone'];
    $organizedUsers[$user['user_id']]['documents'][] = [
        'id' => $user['doc_id'],
        'date' => $user['date'],
        'type' => $user['type'],
        'status' => $user['status'],
        'fileName' => $user['fileName'],
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Documents</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Gestion des Documents par Utilisateur</h1>

    <?php foreach ($organizedUsers as $userId => $user): ?>
        <div class="user-section">
            <h2><?= htmlspecialchars($user['name']) ?> - <?= htmlspecialchars($user['phone']) ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type de Document</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user['documents'] as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['date']) ?></td>
                            <td><?= htmlspecialchars($doc['type']) ?></td>
                            <td><?= htmlspecialchars($doc['status']) ?></td>
                            <td>
                                <a href="view_document.php?doc_id=<?= $doc['id'] ?>">Voir</a>
                                <a href="download_document.php?doc_id=<?= $doc['id'] ?>">Télécharger</a>
                                <a href="delete_document.php?doc_id=<?= $doc['id'] ?>">Supprimer</a>
                                <?php if ($doc['status'] == 'pending'): ?>
                                    <a href="approve_document.php?doc_id=<?= $doc['id'] ?>">Approuver</a>
                                    <a href="reject_document.php?doc_id=<?= $doc['id'] ?>">Rejeter</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

</body>
</html>


approve_document.php

<?php
include 'db.php';

// Vérifier si un ID de document est fourni
if (isset($_GET['doc_id'])) {
    $docId = $_GET['doc_id'];

    // Mettre à jour le statut du document
    $query = "UPDATE documents SET status = 'approved' WHERE id = :doc_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc_id', $docId, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger vers la page principale après l'approbation
    header('Location: index.php');
    exit;
}
?>


reject_document.php

<?php
include 'db.php';

// Vérifier si un ID de document est fourni
if (isset($_GET['doc_id'])) {
    $docId = $_GET['doc_id'];

    // Mettre à jour le statut du document
    $query = "UPDATE documents SET status = 'rejected' WHERE id = :doc_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc_id', $docId, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger vers la page principale après le rejet
    header('Location: index.php');
    exit;
}
?>



delete_document.php

<?php
include 'db.php';

// Vérifier si un ID de document est fourni
if (isset($_GET['doc_id'])) {
    $docId = $_GET['doc_id'];

    // Supprimer le document
    $query = "DELETE FROM documents WHERE id = :doc_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc_id', $docId, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger vers la page principale après la suppression
    header('Location: index.php');
    exit;
}
?>

view_document.php

<?php
include 'db.php';

// Vérifier si un ID de document est fourni
if (isset($_GET['doc_id'])) {
    $docId = $_GET['doc_id'];

    // Récupérer les détails du document
    $query = "SELECT * FROM documents WHERE id = :doc_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc_id', $docId, PDO::PARAM_INT);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document) {
        echo "<h1>Détails du document</h1>";
        echo "<p><strong>Type:</strong> " . htmlspecialchars($document['type']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($document['date']) . "</p>";
        echo "<p><strong>Statut:</strong> " . htmlspecialchars($document['status']) . "</p>";
        echo "<p><strong>Nom du fichier:</strong> " . htmlspecialchars($document['fileName']) . "</p>";
    } else {
        echo "<p>Document non trouvé.</p>";
    }
}
?>


download_document.php

<?php
include 'db.php';

// Vérifier si un ID de document est fourni
if (isset($_GET['doc_id'])) {
    $docId = $_GET['doc_id'];

    // Récupérer le document
    $query = "SELECT * FROM documents WHERE id = :doc_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc_id', $docId, PDO::PARAM_INT);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document) {
        $filePath = 'uploads/' . $document['fileName']; // Dossier où les fichiers sont stockés

        if (file_exists($filePath)) {
            // Forcer le téléchargement du fichier
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($filePath));
            ob_clean();
            flush();
            readfile($filePath);
            exit;
        } else {
            echo "Le fichier n'existe pas sur le serveur.";
        }
    } else {
        echo "Document non trouvé dans la base de données.";
    }
} else {
    echo "ID de document non spécifié.";
}
?>


