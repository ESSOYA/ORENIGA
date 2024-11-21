<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Assurez-vous que l'utilisateur est connecté et que son ID est défini dans la session
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user']['admin_id'];

// Gérer le téléversement de la photo de profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo_profil'])) {
    $target_dir = "PROFIL/";
    $file_name = basename($_FILES["photo_profil"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Vérifier si le fichier est une image
    $check = getimagesize($_FILES["photo_profil"]["tmp_name"]);
    if ($check === false) {
        echo "Le fichier n'est pas une image.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier (limite à 5000 Ko)
    if ($_FILES["photo_profil"]["size"] > 5000000) {
        echo "Le fichier est trop volumineux.";
        $uploadOk = 0;
    }

    // Vérifier le format du fichier
    if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        echo "Seuls les formats JPG, JPEG et PNG sont autorisés.";
        $uploadOk = 0;
    }

    // Téléverser le fichier et enregistrer le chemin dans la base de données
    if ($uploadOk === 1) {
        if (move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $target_file)) {
            $sql = "UPDATE administrateur SET Photo_profil = ? WHERE admin_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);

            if ($stmt->execute()) {
                echo "Photo de profil mise à jour avec succès.";
                header("Location: administrateur.php");
                exit();
            } else {
                echo "Erreur lors de la mise à jour de la base de données : " . $stmt->error;
            }
        } else {
            echo "Erreur lors du téléchargement de la photo.";
        }
    } else {
        echo "Téléversement échoué.";
    }
}

$conn->close();
?>
