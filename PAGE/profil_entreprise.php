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
$sql = "SELECT Nom_famille, prénom, username,genre,  DATE_NAISSANCE, email,téléphone ,photo_profil, entre_type FROM entreprises WHERE entre_id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom, $username, $genre, $date_naissance, $email, $phone, $photo_profil, $entre_type);
$stmt->fetch();
$stmt->close();

// Afficher le profil de l'utilisateur
// Ici, vous pouvez créer votre HTML pour afficher les informations de l'utilisateur


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
            margin-top: -1px;
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
        <h2><?php echo htmlspecialchars($username) ; ?></h2>
        <h2></h2>
        <strong><p><?php echo htmlspecialchars($entre_type);?></p></strong>
    </div>

    <!-- Boutons en haut -->
    <div class="profile-buttons">
       
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
            <li><strong>👶: Pas de date de naissance.</strong></li>
            <li><strong>🧑‍🦱/👩 : Pas de genre.</strong></li>
            <li><strong>📍 : libreville/Gabon</strong></li>

        </ul>
    </div>

    <div class="profile-section">
        <h3>À Propos</h3>
        <p><strong>Nous sommes une entreprise spécialisée dans notre domaine, et nous avons une multitude de solutions et de services à vous proposer. Notre objectif est de répondre à vos besoins avec des offres personnalisées, conçues pour vous aider à atteindre vos objectifs. Que vous recherchiez de l'expertise, des ressources innovantes ou un accompagnement sur mesure, nous mettons tout en œuvre pour vous fournir des solutions de qualité. Travailler avec nous, c'est choisir une équipe engagée et prête à faire de votre projet un succès.</strong></p>
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
