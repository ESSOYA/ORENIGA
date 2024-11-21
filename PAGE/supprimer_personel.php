<?php
// Informations de connexion à la base de données
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ORENIGA';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier que l'ID de l'enseignant est passé en POST
if (isset($_POST['id'])) {
    $ens_id = $conn->real_escape_string($_POST['id']);

    // Supprimer l'enseignant de la base de données en fonction de son ID
    $sql = "DELETE FROM personel WHERE pers_id = '$ens_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Enseignant supprimé avec succès.";
    } else {
        echo "Erreur lors de la suppression de l'enseignant : " . $conn->error;
    }
} else {
    echo "Aucun ID d'enseignant fourni.";
}

$conn->close();

// Redirection vers la page des enseignants après suppression
header("Location: admin.php"); // Remplacez par la page de la liste des enseignants
exit();
?>
