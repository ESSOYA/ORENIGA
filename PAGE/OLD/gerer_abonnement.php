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

$user_id = $_SESSION['user_id']; 
$profile_id = intval($_POST['profile_id']);

// Vérifier l'état actuel de l'abonnement
$check_sql = "SELECT COUNT(*) FROM abonnements WHERE abonne_id = ? AND suivi_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $profile_id);
$check_stmt->execute();
$check_stmt->bind_result($is_abonne);
$check_stmt->fetch();
$check_stmt->close();

if ($is_abonne) {
    // Si déjà abonné, on supprime l'abonnement
    $delete_sql = "DELETE FROM abonnements WHERE abonne_id = ? AND suivi_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $profile_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    echo "abonner";
} else {
    // Sinon, on ajoute un abonnement
    $insert_sql = "INSERT INTO abonnements (abonne_id, suivi_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $profile_id);
    $insert_stmt->execute();
    $insert_stmt->close();
    echo "desabonner";
}
$conn->close();
