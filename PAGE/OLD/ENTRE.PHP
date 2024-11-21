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
$user_id = $_SESSION['user']['entre_id']; // Assurez-vous que l'ID de l'utilisateur est stocké ici
$sql = "SELECT Nom_famille, prénom,username,DATE_NAISSANCE, email, photo_profil, mot_de_passe,entre_type FROM entreprises WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
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
        header("Location: entre.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $BIRTH = $conn->real_escape_string($_POST['BIRTH']);
   
        $sql = "UPDATE entreprises SET DATE_NAISSANCE = ? WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $BIRTH, $user_id);
        $stmt->execute();
        $_SESSION['message'] = "La DATE DE NAISSANCE  a été modifiée avec succès.";

    // Rediriger pour éviter la soumission répétée du formulaire
    header("Location: entre.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ENTREPRISE</title>
    <style>
        /* Global styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            color: #333;
            background-color: #ffffff;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6a5af9;
            display: flex;
            align-items: center;
            
        }

        header .logo img {
    
        margin-right: 19px;
        
        vertical-align: middle;
        border-radius: 10%;
    
        }

        header .logo a {
            display: inline-block;
            align-items: center;
            color: #111;
            font-size: small;
        }

        nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #333;
            position: relative;
            transition: color 0.3s ease;
            
        }

        /* Hover effect for navigation links */
        nav a:hover {
            color: #6a5af9;
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #6a5af9;
            bottom: -5px;
            left: 0;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        nav a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background-color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
            
            color: #29d9d5;
            
        }

        .sidebar button {
            background-color: transparent;
            color: #333;
            border: none;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 16px;
            text-align: left;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar button:hover {
            background-color: #f0f2f5;
            color: #6a5af9;
        }

        /* Dashboard */
        .dashboard {
            padding: 20px;
            flex: 1;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            width: 30%;
            font-size: 24px;
            font-weight: bold;
            color: #6a5af9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat span {
            display: block;
            margin-top: 10px;
            font-size: 16px;
            font-weight: normal;
            color: #333;
        }

        /* Hover effect for stats */
        .stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Upcoming Consultations */
        .recent-consultations {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recent-consultations h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #6a5af9;
        }

        .consultation-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }

        .consultation-card:hover {
            background-color: #f0f2f5;
        }

        .consultation-card div {
            display: flex;
            align-items: center;
        }

        .profile-icon {
            background-color: #6a5af9;
            color: #fff;
            font-size: 14px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 10px;
        }

        .badge {
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Badge colors */
        .badge.scheduled { background-color: #ffc107; }
        .badge.pending { background-color: #f44336; }
        .badge.completed { background-color: #4caf50; }

        /* Layout */
        main {
            display: flex;
            margin-top: 1px;
            background-color: rgba(0, 0, 0, 0.15);
        }

        @keyframes gradientBackground {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
        }

        .container {

        margin-top: 1000px;
        max-width: 1090px;
        min-width: 1090px;
        max-height: AUTO;
        margin: auto;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 15px;
        background-color: rgb(255, 255, 255);
        
        animation: gradientBackground 1s ease infinite;
        
        padding: 100px;
        border-radius: 15px;
        backdrop-filter: blur(15px);
        width: 90%;
        animation: fadeIn 1s ease-out forwards;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        animation: fadeIn 5s ease-out forwards;
        margin-bottom: 100px;
            }
        

        .container .bienvenu{
            margin-top: 10000px;
            max-width: 900px;
            max-height: AUTO;
            margin: auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 15px;
            
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(15px);
            width: 90%;
        
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: #000000;
            animation: fadeIn 5s ease-out forwards;
            margin-bottom: 100px;
        }

        .container .info{
            
            max-width: 900px;
            max-height: AUTO;
            margin: auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 15px;
            
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(15px);
            width: 90%;
        
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: #000000;
            animation: fadeIn 5s ease-out forwards;
            margin-bottom: 100px;
        }

        .container .info .img {
            
            max-width: 10px;
        }


        input[type="file"] {
         margin: 10px 0;
        }

        input[type="date"] {
            margin: 10px 0;
            backdrop-filter: blur(15px);
            width: 350px;
            text-size-adjust: 100%;
            padding: 1%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            
            animation: fadeIn 5s ease-out forwards;
        }
        img {
            max-width: 250px;
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
        .info{
            
        color: rgb(0, 0, 0);
            
        }
        a{
            color: rgb(17, 0, 255);
        
        }
        .radio-group {
            display: flex;
            margin-bottom: 10px; 
            margin-left: 15%;
            margin-right: 15%;
            justify-content: space-between;

        }
        .radio-group input {
            margin-right: 5px;
            text-align: center;


        }
        .input{
            text-align: center;
            padding: 8px;
        }
        select{
            background-color:#098909;
            cursor: pointer;
        }

        .bienvenu{
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            top: -100px;
        }

        .co{
            color: #111;
            text-shadow: #098909;
            text-rendering: auto;
            text-overflow: clip;
            font-size: x-large;
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        
        }

            .container1 {
        
        margin-top: 100px;
        
        max-width: 900px;
        max-height: AUTO;
        
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 15px;
        
        background-color: rgb(255, 255, 255);
        background-color: rgba(255, 255, 255, 0.15);
        padding: 50px;
        border-radius: 15px;
        backdrop-filter: blur(15px);
        width: 90%;
    
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        color: #111;
        animation: fadeIn 5s ease-out forwards;
        }


                .btn-reservation {
            color: #29d9d5;
            font-size: 14px;
            border: 2px solid #29d9d5;
            padding: 5px 20px;
            transition: 0.5s;
            font-weight: bolder;
        }
        .btn-reservation:hover {
            background-color: #29d9d5;
            color: #fff;
        
        }

        .logo1 {
            
            animation: bounce 2s infinite;
            
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
        <a href="#profil"><img src="<?php echo htmlspecialchars($photo_profil); ?>" width="50" alt="Photo de profil"></a>
        <a href="#profil1">
          <span><strong><?php echo htmlspecialchars($name1); ?></strong></span
          > 
          <span> (<?php echo htmlspecialchars($type); ?>) </span> </a
        > 
        </div>
        <img src="IMAGE/ORENIGA_FULL.png" width="80" alt="ORENIGA" class="logo1">
        <section id="nav"><nav>
            <a href="/orenigo/index.html">Accueuil</a>
            <a href="#">Forum</a>
            <a href="discussion_pro2.php">publier</a>
            <a href="logout.php">Déconnexion</a>
        </nav></section>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Navigation</h2>
            <a href="#nav"><button>Dashboard</button></a>
            <a href="#profil"><button>mes informations</button></a>
            <a href="#profil1"><button>MODIFIER LE PROFIL</button></a>
            <a href="#profil2"><button>VOIR LES MESSAGES</button></a>
            
        </aside>

        <!-- Dashboard -->
        <section class="dashboard" id="dashboard">
           
            <div class="recent-consultations">
            <div class="container">
        
        <div class="bienvenu" >
        <h1>LE TABLEAU DE BORD POUR VOTRE ENTREPRISE</h1> <br>
            <H2>Bienvenu</H2>
            <strong>BONJOUR </strong> <?php echo htmlspecialchars($name1); ?> 🙌🙌 <strong> J'ose esprer que vous vous portez bien ? vous venez vous connecté aujourd'hui pour un but precis ? </strong><br><br>
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
                <img src="<?php echo htmlspecialchars($photo_profil); ?>" width="300" alt="Photo de profil">
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
                <form action="entre.php" method="post" enctype="multipart/form-data">
                    <label for="photo_profil">Choisir une photo :</label>
                    <input type="file" name="photo_profil" id="photo_profil" accept=".jpg,.jpeg,.png" required>
                    <button type="submit">Mettre à jour</button>
                </form>
            <h2>Mettre à jour la date de naissance</h2>
                <form action="entre.php" method="post" enctype="multipart/form-data">
                <div class="option">
                    
                            <input type="date"  id="BIRTH" name="BIRTH" required>
                    
                    <button type="submit">Mettre à jour</button>
                </form>

            
       </div>
       </section>
       <section id="profil2">
       <DIV class="container1" >
        <H1>VOS MESSAGES S'AFFICHERONT ICI</H1> 

    </DIV>
       </section>
    <br><br>
    <h3>vous êtes connecté(e) en tant que :</h3>
    <div class="co">
    <?php echo htmlspecialchars($name1); ?> <br> <br></div>
    <a href="logout.php" class="btn-reservation"
        >Se déconnecter</a
      >
    
    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
