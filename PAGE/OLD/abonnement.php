<?php
session_start();

$host = 'localhost';
$dbname = 'orenigo';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les données envoyées par AJAX
$user_id = $_SESSION['etu_id'] ?? 0; // ID de l'utilisateur connecté
$profile_id = $_POST['profile_id'] ?? 0; // ID de l'utilisateur à suivre
$action = $_POST['action'] ?? ''; // Action (abonner ou désabonner)

// Vérifier que les IDs sont valides
if ($user_id && $profile_id && $action) {
    if ($action === 'subscribe') {
        // Ajoute l'abonnement
        $sql = "INSERT INTO abonnements (abonne_id, cible_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $profile_id);
        if ($stmt->execute()) {
            echo "abonné";
        } else {
            echo "Erreur d'abonnement";
        }
        $stmt->close();
    } elseif ($action === 'unsubscribe') {
        // Supprime l'abonnement
        $sql = "DELETE FROM abonnements WHERE abonne_id = ? AND cible_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $profile_id);
        if ($stmt->execute()) {
            echo "désabonné";
        } else {
            echo "Erreur de désabonnement";
        }
        $stmt->close();
    }
} else {
    echo "Erreur : données manquantes";
}

// Assurez-vous que $user_id est l'ID de l'utilisateur connecté
$user_id = $_SESSION['etu_id'];  // Remplace par la méthode que tu utilises pour obtenir l'ID de l'utilisateur connecté

// Vérifier si l'utilisateur est déjà abonné
$abonnement_check_sql = "SELECT COUNT(*) FROM abonnements WHERE abonne_id = ? AND cible_id = ?";
$abonnement_check_stmt = $conn->prepare($abonnement_check_sql);
$abonnement_check_stmt->bind_param("ii", $user_id, $profile_id);
$abonnement_check_stmt->execute();
$abonnement_check_stmt->bind_result($is_abonne);
$abonnement_check_stmt->fetch();
$abonnement_check_stmt->close();


$conn->close();
?>
