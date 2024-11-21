<?php
session_start();

$host = 'localhost';
$dbname = 'oreniga'; // Nom de votre base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = ''; // Mot de passe de la base de données

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user']['entre_id']; // Assurez-vous que l'ID de l'utilisateur est stocké ici
$sql = "SELECT Nom_famille, prénom, email, photo_profil FROM entreprises WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom, $email, $photo_profil);
$stmt->fetch();
$stmt->close();

// Gérer le téléchargement de la photo de profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo_profil'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["photo_profil"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Vérifier si l'image est une vraie image
    $check = getimagesize($_FILES["photo_profil"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['message'] = "Ce n'est pas une image.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier
    if ($_FILES["photo_profil"]["size"] > 500000) {
        $_SESSION['message'] = "Désolé, votre fichier est trop gros.";
        $uploadOk = 0;
    }

    // Vérifier le format du fichier
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $_SESSION['message'] = "Désolé, seuls les fichiers JPG, JPEG et PNG sont autorisés.";
        $uploadOk = 0;
    }

    // Si tout est bon, essayer de télécharger le fichier
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $target_file)) {
            // Mettre à jour le chemin de la photo de profil dans la base de données
            $sql = "UPDATE entreprises SET photo_profil = ? WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $_SESSION['message'] = "La photo de profil a été mise à jour.";
        } else {
            $_SESSION['message'] = "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
        }
        // Rediriger pour éviter la soumission répétée du formulaire
        header("Location: entreprise.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        img {
            max-width: 150px;
            height: auto;
        }
        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            color: red; /* Couleur pour les messages d'erreur */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mon Profil ENTREPRISE</h1>
        <div>
            <strong>Nom :</strong> <?php echo htmlspecialchars($nom); ?><br>
            <strong>Prénom :</strong> <?php echo htmlspecialchars($prenom); ?><br>
            <strong>Email :</strong> <?php echo htmlspecialchars($email); ?><br>
            <strong>Photo de Profil :</strong><br>
            <?php if ($photo_profil): ?>
                <img src="<?php echo htmlspecialchars($photo_profil); ?>" alt="Photo de profil">
            <?php else: ?>
                Pas de photo de profil.
            <?php endif; ?>
        </div>

        <?php
        // Afficher les messages d'erreur ou de succès
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']); // Supprimer le message après l'affichage
        }
        ?>

        <h2>Mettre à jour la photo de profil</h2>
        <form action="entreprise.php" method="post" enctype="multipart/form-data">
            <label for="photo_profil">Choisir une photo :</label>
            <input type="file" name="photo_profil" id="photo_profil" accept=".jpg,.jpeg,.png" required>
            <button type="submit">Mettre à jour</button>
        </form>

        <h2>Discussion avec d'autres utilisateurs</h2>
        <a href="discussion2.php">Accéder à la discussion</a>
        <br><a href="discussion_pro2.php"><button class="button" onclick="window.location.href='discussion_pro2.php'" >PUBLIER UNE ANNONCE </button></a>

        <a href="logout.php"><button class="button" onclick="window.location.href='logout.php'" >Se déconnecter</button></a>
        <a href="/ORENIGA/INDEX.HTML"><button class="button" onclick="window.location.href='/ORENIGA/INDEX.HTML'" >Revenir au menu principal</button></a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
