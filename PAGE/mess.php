<?php

// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "ORENIGA"; // Remplacez par le nom de votre base de données

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Préparer la requête SQL pour insérer les données dans la base de données
    $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";

    // Utiliser une requête préparée pour éviter les injections SQL
    if ($stmt = $conn->prepare($sql)) {
        // Lier les paramètres
        $stmt->bind_param("sss", $name, $email, $message);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo "Message envoyé avec succès! OUI OUI";
            header("Location: /ORENIGA/INDEX.HTML");
        } else {
            echo "Erreur: " . $stmt->error;
        }

        // Fermer la requête préparée
        $stmt->close();
    }

    // Fermer la connexion à la base de données
    $conn->close();
}
?>
