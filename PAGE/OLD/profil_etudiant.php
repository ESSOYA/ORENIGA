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

// Récupérer l'ID de l'utilisateur à afficher
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer les informations de l'utilisateur
$sql = "SELECT Nom_famille, prénom, username,genre,  DATE_NAISSANCE, email,téléphone ,photo_profil, etu_type FROM etudiants WHERE etu_id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom, $username, $genre, $date_naissance, $email, $phone, $photo_profil, $etu_type);
$stmt->fetch();
$stmt->close();

// Afficher le profil de l'utilisateur
// Ici, vous pouvez créer votre HTML pour afficher les informations de l'utilisateur
// Compter le nombre d'abonnés de l'utilisateur (par exemple dans abonnement.php)
$abonne_count_sql = "SELECT COUNT(*) FROM abonnements WHERE cible_id = ?";
$abonne_count_stmt = $conn->prepare($abonne_count_sql);
$abonne_count_stmt->bind_param("i", $profile_id); // L'ID de l'utilisateur dont on veut voir les abonnés
$abonne_count_stmt->execute();
$abonne_count_stmt->bind_result($abonne_count);
$abonne_count_stmt->fetch();
$abonne_count_stmt->close();


?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200vh;
            padding: 20px;
        }

        .profile-container {
            background-color: #fff;
            width: 1200px;
            min-height: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background-color: #4CAF50;
        }

        .profile-header h2 {
            margin-top: 15px;
            font-size: 1.8em;
            color: #333;
        }

        .profile-header p {
            color: #777;
        }

        .profile-stats {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-top: 15px;
            color: #333;
        }

        .profile-stats div {
            text-align: center;
        }

        .profile-stats div span {
            display: block;
            font-weight: bold;
            font-size: 1.1em;
        }

        .profile-section {
            width: 100%;
            margin-top: 15px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            margin-bottom: 15px;
        }

        .profile-section h3 {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-section p, .profile-section ul {
            color: #555;
            font-size: 0.95em;
            line-height: 1.6;
        }

        .profile-section ul {
            list-style-type: none;
            padding-left: 0;
        }

        .profile-section ul li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .profile-buttons button {
            padding: 10px 20px;
            font-size: 1em;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .subscribe-btn {
            background-color: #4CAF50;
        }

        .subscribe-btn:hover {
            background-color: #45a049;
        }

        .message-btn {
            background-color: #2196F3;
        }

        .message-btn:hover {
            background-color: #1e88e5;
        }

        .view-posts-btn {
            background-color: #f44336;
        }

        .view-posts-btn:hover {
            background-color: #e53935;
        }

        .back-button {
            margin-top: 20px;
            padding: 10px 30px;
            font-size: 1em;
            color: #333;
            background-color: #e0e0e0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #d5d5d5;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
    <a href="#profil"><img src="<?php echo htmlspecialchars($photo_profil); ?>"  alt="Photo de profil"></a>
        <h2><?php echo htmlspecialchars($nom) ; ?> <?php echo htmlspecialchars($prenom) ; ?></h2>
        <h2></h2>
        <strong><p><?php echo htmlspecialchars($etu_type);?></p></strong>
    </div>

    <!-- Boutons en haut -->
    <div class="profile-buttons">
       
        
    </div>
    <div class="profile-buttons">
    <!-- Bouton d'abonnement avec identifiant pour la requête AJAX -->
   
</div>



    <div class="profile-stats">
        
        
    </div>

    <div class="profile-section">
        <h3>Informations Personnelles</h3>
        <ul>
            <li>📧 :<strong><?php echo htmlspecialchars($email);?></strong></li>
            <li><?php if ($phone): ?>
                <strong>📞:<?php echo htmlspecialchars($phone); ?><br>
            <?php else: ?>
               <strong> 📞 : Pas de numéro de téléphone.</strong> 
            <?php endif; ?></li>
            <li><?php if ($date_naissance): ?>
                <strong>👶 :<?php echo htmlspecialchars($date_naissance); ?><br>
            <?php else: ?>
                👶: Pas de date de naissance.</strong> 
            <?php endif; ?></li>
            <li><?php if ($genre): ?>
                <strong>🧑‍🦱/👩 :<?php echo htmlspecialchars($genre); ?><br>
            <?php else: ?>
                🧑‍🦱/👩 : Pas de genre.</strong> 
            <?php endif; ?></li>
            <li><strong>📍 : libreville/Gabon</strong></li>

        </ul>
    </div>

    <div class="profile-section">
        <h3>À Propos</h3>
        <p><strong>Je suis étudiant, et j'apprécie énormément cette expérience.
             Chaque journée m'apporte des connaissances nouvelles et me permet de découvrir des domaines passionnants. J'aime apprendre,
              relever des défis et développer mes compétences pour construire un avenir enrichissant. L'université est pour moi bien plus qu'un lieu d'étude;
               c'est un espace d'échange, d'innovation et de découverte qui me motive à m'investir pleinement.</strong></p>
    </div>

    <div class="profile-section">
        <h3>Compétences</h3>
        <ul>
            
                <li>💻 : <strong>pas de compétences !</strong></li>
            
        </ul>
    </div>

    <!-- Bouton "Revenir en arrière" en bas -->
    <button class="back-button" onclick="history.back()">Revenir en arrière</button>
</div>

</body>
</html>
