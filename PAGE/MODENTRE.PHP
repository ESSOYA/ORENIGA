<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ORENIGA';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Vérifier si l'utilisateur est connecté et récupérer son ID
if (isset($_SESSION['user'])) {
    $user_Id = $_SESSION['user']['entre_id'];

    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM entreprise WHERE entre_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Utilisateur non trouvé.");
    }
} else {
    die("Utilisateur non connecté.");
}

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $famille = $conn->real_escape_string($_POST['famille']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $info = $conn->real_escape_string($_POST['info']);
    $marketing = $conn->real_escape_string($_POST['marketing']);
    $digital = $conn->real_escape_string($_POST['digital']);
    $TYPO = $conn->real_escape_string($_POST['TYPO']);
 

    // Mettre à jour les informations de l'utilisateur
    $updateSql = "UPDATE entreprise SET Prenom=?, Nom_famille=? ,Type_entre=?, Email=?, Téléphone=?, DATE_NAISSANCE=?,Bio=?, Competence_info=?, Competence_marketing=?, Competence_digital=? WHERE entre_id=?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssssssssi", $prenom, $famille, $TYPO, $email, $phone, $dob,$bio, $info, $marketing, $digital, $user_Id); // Remplace $userId par $user_Id
    


    if ($updateStmt->execute()) {
        echo "<p>Informations mises à jour avec succès.</p>";
        header("Location: /oreniga/page/entreprise.php");
        exit; 
    } else {
        echo "<p>Erreur lors de la mise à jour des informations: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
