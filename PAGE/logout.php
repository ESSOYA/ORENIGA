<?php
session_start(); // Démarre la session

// Détruire toutes les données de la session
$_SESSION = array(); // Vide la session

// Si vous voulez détruire complètement la session, également supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy(); // Détruit la session

// Redirige vers la page d'accueil ou la page de connexion

header("Location: login.php"); // Remplacez par la page vers laquelle vous souhaitez rediriger
exit();
?>
