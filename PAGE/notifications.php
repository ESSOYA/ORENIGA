<?php
session_start();
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si l'étudiant est connecté et obtenir son ID
if (!isset($_SESSION['etu_id'])) {
    die("Vous devez être connecté pour voir vos notifications.");
}

$etu_id = $_SESSION['etu_id']; // Récupère l'ID de l'étudiant depuis la session

// Récupérer les notifications non lues pour l'étudiant
$query = "SELECT * FROM notification_personel WHERE etu_id = ? AND status = 'non_lu' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $etu_id);
$stmt->execute();
$result = $stmt->get_result();

// Si des notifications existent
if ($result->num_rows > 0) {
    while ($notification = $result->fetch_assoc()) {
        $message = $notification['message'];
        $created_at = $notification['created_at'];

        // Afficher chaque notification
        echo "<div class='notification'>";
        echo "<p><strong>Message :</strong> $message</p>";
        echo "<p><small>Envoyé le : $created_at</small></p>";
        echo "</div>";

        // Mettre à jour le statut de la notification à 'lu' après l'affichage
        $updateQuery = "UPDATE notification_personel SET status = 'lu' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('i', $notification['id']);
        $updateStmt->execute();
    }
} else {
    echo "<p>Vous n'avez aucune notification.</p>";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Conteneur des notifications */
.notification {
    background-color: #f9f9f9;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}

/* Titre de la notification */
.notification p {
    font-size: 16px;
    color: #333;
}

/* Message de la notification */
.notification p strong {
    font-weight: bold;
}

/* Date de la notification */
.notification small {
    color: #777;
    font-size: 12px;
}

/* Si la notification est non lue, on peut la styliser différemment */
.notification.unread {
    background-color: #e8f0fe;
    border-left: 5px solid #4285f4;
}

.notification.unread p {
    font-weight: bold;
}

    </style>
</head>
<body>
    
</body>
</html>