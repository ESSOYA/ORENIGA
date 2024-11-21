<?php
session_start();

$host = 'localhost';
$dbname = 'oreniga'; // Nom de votre base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = ''; // Mot de passe de la base de données

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user']['etu_id']; // Assurez-vous que 'etu_id' est bien défini dans la session

// Récupérer les informations nécessaires pour la redirection
$sql = "SELECT BAC, filiere FROM etudiant WHERE etu_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bac, $filiere);
$stmt->fetch();
$stmt->close();

// Vérifier la colonne filière
if (empty($filiere)) {
    // Si la filière est vide, rediriger vers la page d'ajout de filière
    header("Location: ajouter_filiere.php");
    exit();
}

// Rediriger selon le BAC et la filière
if ($bac === 'SCIENTIFIQUE(C)' || $bac === 'SCIENTIFIQUE(D)') {
    // Les scientifiques peuvent être dans RT, GI ou MTIC
    header("Location: inscription_concours.php");
    exit();
} elseif ($bac === 'ECONOMISTE(B)' || $bac === 'LITTERAIRE(A1)') {
    // Les économistes et littéraires ne peuvent être que dans MTIC
    if ($filiere === 'MTIC') {
        header("Location: inscription_concours.php");
        exit();
    } else {
        echo "Erreur : Filière non autorisée pour ce type de BAC.";
    }
} else {
    echo "Erreur : Type de BAC non reconnu.";
}

// Fermer la connexion
$conn->close();
?>
