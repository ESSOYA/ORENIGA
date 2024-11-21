<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "oreniga";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Récupérer les données du formulaire
$oldPassword = $_POST['oldPassword'];
$newPassword = $_POST['newPassword'];

// Id de l'utilisateur (ajustez ceci selon votre système de session)
session_start();
$userId = $_SESSION['pers_id']; // l'identifiant de l'utilisateur connecté

// Requête pour obtenir l'ancien mot de passe haché depuis la base de données
$sql = "SELECT Mot_de_passe FROM personel WHERE pers_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPassword = $row['Mot_de_passe'];

    // Vérifier si l'ancien mot de passe correspond
    if (password_verify($oldPassword, $hashedPassword)) {
        // Hacher le nouveau mot de passe
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Mettre à jour le mot de passe dans la base de données
        $updateSql = "UPDATE personel SET Mot_de_passe = ? WHERE pers_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newHashedPassword, $userId);

        if ($updateStmt->execute()) {
            echo "Mot de passe mis à jour avec succès.";
            echo "<script>
        alert('Mot de passe mis à jour avec succès.');
        window.location.href = '/oreniga/page/personel.php';
      </script>";
        } else {
            
            echo "<script>
        alert('Erreur lors de la mise à jour du mot de passe.');
        window.location.href = '/oreniga/page/personel.php';
      </script>";
        }

        $updateStmt->close();
    } else {
        echo "<script>
        alert('L\'ancien mot de passe est incorrect.');
        window.location.href = '/oreniga/page/personel.php';
      </script>";
    }
} else {
    
    echo "<script>
        alert('Utilisateur non trouvé.');
        window.location.href = '/oreniga/page/personel.php';
      </script>";
}

$stmt->close();
$conn->close();
?>
