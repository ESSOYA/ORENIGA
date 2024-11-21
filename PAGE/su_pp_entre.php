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

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    $user_Id = $_SESSION['user']['entre_id']; // Id de l'utilisateur connecté

    // Récupérer le chemin de l'image depuis la base de données
    $sql = "SELECT Photo_profil FROM entreprise WHERE entre_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $photo_path = $row['Photo_profil'];

        // Vérifier si l'image existe sur le serveur et la supprimer
        if (!empty($photo_path) && file_exists($photo_path)) {
            unlink($photo_path); // Supprime l'image du serveur
        }

        // Mettre à jour la base de données pour supprimer le chemin de l'image
        $updateSql = "UPDATE entreprise SET Photo_profil = NULL WHERE entre_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $user_Id);

        if ($updateStmt->execute()) {
            
            echo "<script>
            alert('Photo de profil supprimée avec succès.');
            window.location.href = '/oreniga/page/entreprise.php';
          </script>";
        } else {
            
            echo "<script>
            alert('Erreur lors de la mise à jour de la base de données.');
            window.location.href = '/oreniga/page/entreprise.php';
          </script>";
        }
    } else {
        
        echo "<script>
        alert('Aucune photo de profil trouvée pour cet utilisateur.');
        window.location.href = '/oreniga/page/entreprise.php';
      </script>";
    }
} else {
   
    echo "<script>
        alert('Utilisateur non connecté.');
        window.location.href = '/oreniga/page/entreprise.php';
      </script>";
}

$conn->close();
?>
