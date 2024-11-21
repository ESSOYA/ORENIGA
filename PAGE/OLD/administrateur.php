<?php
session_start();

$host = 'localhost';
$dbname = 'orenigo'; // Nom de votre base de données
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
$user_id = $_SESSION['user']['admin_id']; // Assurez-vous que l'ID de l'utilisateur est stocké ici
$sql = "SELECT Nom_famille, prénom,username,DATE_NAISSANCE, email, photo_profil, mot_de_passe,admin_type FROM administrateur WHERE admin_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom,$name1,$DATE, $email, $photo_profil, $pass,$type );
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
    if ($_FILES["photo_profil"]["size"] > 500000000) {
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
            $sql = "UPDATE administrateur SET photo_profil = ? WHERE admin_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $_SESSION['message'] = "La photo de profil a été mise à jour.";
        } else {
            $_SESSION['message'] = "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
        }
        // Rediriger pour éviter la soumission répétée du formulaire
        header("Location: administrateur.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $BIRTH = $conn->real_escape_string($_POST['BIRTH']);
   
        $sql = "UPDATE administrateur SET DATE_NAISSANCE = ? WHERE admin_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $BIRTH, $user_id);
        $stmt->execute();
        $_SESSION['message'] = "La DATE DE NAISSANCE  a été modifiée avec succès.";

    // Rediriger pour éviter la soumission répétée du formulaire
    header("Location: administrateur.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETUDIANT</title>
    <link rel="stylesheet" href="administrateur.css">
   
</head>
<body>
<header>
      <div class="logo">
      <a href="#profil"><img src="<?php echo htmlspecialchars($photo_profil); ?>" width="75" alt="Photo de profil"></a>
        <a href="#profil1">
          <span><strong><?php echo htmlspecialchars($name1); ?></strong></span
          > </a
        > <span> (<?php echo htmlspecialchars($type); ?>) <br></span>
        <div class="lo" ><img src="IMAGE/ORENIGA_FULL.png" width="80" alt="ORENIGA" class="floating-logo"></div>
      </div>
      <ul class="menu">
        <li>
          <a href="/ORENIGO/INDEX.HTML"><strong>Acceuil</strong></a>
        </li>
        <li>
          <a href="dashadmin.php"><strong>Forums</strong></a>
        </li>
        <li>
          <a href="ADMIN.PHP"><strong>tableau de bord</strong></a>
        </li>
        <li>
          <a href="discussion_pro1.php"><strong>Entreprises</strong></a>
        </li>
      </ul>
      <a href="logout.php" class="btn-reservation"
        >Se déconnecter</a
      >

      <div class="responsive-menu"></div>
    </header>
    <div class="container">

    
        
        <div class="bienvenu" >
        <h1>Mon Profil ETUDIANT</h1>
            <H2>bienvenu</H2>
            <strong>BONJOUR </strong> <?php echo htmlspecialchars($name1); ?>😎😎 <strong>ça va bien ? vous venez vous connecté aujourd'hui hum c'est super</strong><br><br>
            <STRong>Ci-dessous vous pouvez voir vos informations</STRong>
            <br> <br>
            <STRong>Quand vous publiez les autres utilisateurs pourront voir votre nom complet et ils pourront voir que vous êtes étudiant donc ça ne sert à rien de mentir dans les forums que vous avez de l'argent😊👌</STRong>
            <br><br><br>
        </div>
       <section id="profil">
       <div class="info" >
            <h1>MES INFORMATIONS PERSONNELLES</h1>
            <strong>vous êtes  :</strong> <?php echo htmlspecialchars($type); ?><br><br>
            <strong>Votre Nom :</strong> <?php echo htmlspecialchars($nom); ?><br><br>
            <strong>Votre Prénom :</strong> <?php echo htmlspecialchars($prenom); ?><br><br>
            <strong>Votre Nom d'utilisateur :</strong> <?php echo htmlspecialchars($name1); ?><br><br>
            <strong>votre Adresse mail(Email) :</strong> <?php echo htmlspecialchars($email); ?><br><br>
            <?php if ($DATE): ?>
                <strong>Votre DATE DE NAISSANCE :</strong> <?php echo htmlspecialchars($DATE); ?><br>
            <?php else: ?>
                Pas de date de naissance.
            <?php endif; ?>
            <br><strong>Photo de Profil :</strong><br>
            <?php if ($photo_profil): ?>
                <img src="<?php echo htmlspecialchars($photo_profil); ?>" alt="Photo de profil">
            <?php else: ?>
                Pas de photo de profil.
            <?php endif; ?>
            
        </div>
       </section>


        <?php
        // Afficher les messages d'erreur ou de succès
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']); // Supprimer le message après l'affichage
        }
        ?>


       <section id="profil1" >
       <div class="info" >
            <H1>MISE A JOUR DU PROFIL</H1>
            <h2>Veuillez mettre à jours vos informations </h2>
                <form action="administrateur.php" method="post" enctype="multipart/form-data">
                    <label for="photo_profil">Choisir une photo :</label>
                    <input type="file" name="photo_profil" id="photo_profil" accept=".jpg,.jpeg,.png" required>
                    <button type="submit">Mettre à jour</button>
                </form>
            <h2>Mettre à jour la date de naissance</h2>
                <form action="administrateur.php" method="post" enctype="multipart/form-data">
                <div class="option">
                    
                            <input type="date"  id="BIRTH" name="BIRTH" required>
                    
                    <button type="submit">Mettre à jour</button>
                </form>

            
       </div>
       </section>
       <DIV class="container1" >
        <H1>VOS MESSAGES S'AFFICHERONT ICI</H1>

    </DIV>
    <h3>vous êtes connecté(e) en tant que :</h3>
    <div class="co">
    <?php echo htmlspecialchars($name1); ?> <br> <br>
    <a href="logout.php" class="btn-reservation"
        >Se déconnecter</a
      >
    </div>
    </div>

   
</body>
</html>

<?php
$conn->close();
?>
