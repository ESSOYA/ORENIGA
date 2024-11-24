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
    $user_Id = $_SESSION['user']['pers_id'];

    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM personel WHERE pers_id = ?";
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
    $ECOLE = $conn->real_escape_string($_POST['ECOLE']);


  

    // Mettre à jour les informations de l'utilisateur
    $updateSql = "UPDATE personel SET Prenom=?, Nom_famille=?, Email=?, Téléphone=?, Ecole=?, DATE_NAISSANCE=?,Bio=?, Competence_info=?, Competence_marketing=?, Competence_digital=? WHERE pers_id=?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssssssssi", $prenom, $famille, $email, $phone, $ECOLE, $dob,$bio, $info, $marketing, $digital, $user_Id); // Remplace $userId par $user_Id
    


    if ($updateStmt->execute()) {
        echo "<p>Informations mises à jour avec succès.</p>";
        header("Location: /oreniga/page/personel.php");
        exit; 
    } else {
        echo "<p>Erreur lors de la mise à jour des informations: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
