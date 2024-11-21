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

// Récupérer l'ID de l'utilisateur à afficher
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer les informations de l'utilisateur
$sql = "SELECT Nom_famille, Prenom,genre,  DATE_NAISSANCE, Email,Téléphone ,photo_profil, admin_type FROM administrateur WHERE admin_id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom,  $genre, $date_naissance, $email, $phone, $photo_profil, $admin_type);
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
            height: 350vh;
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
            margin-BOTTOM: 100px;
            padding: 10px 30px;
            font-size: 1em;
            color: #333;
            background-color: #e0e0e0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            position:fixed;
            right :100px;
        }

        .back-button:hover {
            background-color: #d5d5d5;
        }
    </style>
</head>
<body>
<button class="back-button" onclick="history.back()">Revenir en arrière</button>
<div class="profile-container">
    <div class="profile-header">
    <a href="#profil"><img src="<?php echo htmlspecialchars($photo_profil); ?>"  alt="Photo de profil"></a>
        <h2><?php echo htmlspecialchars($nom) ; ?> </h2>
        <h2></h2>
        <strong><p><?php echo htmlspecialchars($admin_type);?></p></strong>
    </div>
    

    <!-- Boutons en haut -->
    <div class="profile-buttons">
        <button class="subscribe-btn">S'abonner</button>
        <button class="message-btn">Envoyer un message</button>
        <button class="view-posts-btn">Voir les publications</button>
    </div>
<br><br><br>
    <div class="profile-stats">
    <div>
            <span>12K</span>
            Connexions
        </div>
        <div>
            <span>22</span>
            Projets
        </div>
        <div>
            <span>945K</span>
            Abonnés
        </div>
       
    </div>
<br><br>
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
        <p><strong>Bienvenue dans l'antre de l'administrateur. Ici, les lignes de code ne sont pas que des commandes ; elles sont des armes. Tu vois des formulaires, des interfaces, des boutons. Moi, je vois des flux de données , des points d'accès, des portes et des failles à surveiller. Tu as un écran, j'ai un labyrinthe, je la maîtrise jusqu'à la dernière ligne.

Chaque requête, chaque clic, chaque fichier téléchargé laisse une trace. Mon monde est un champ de bataille invisible, et je suis l'œil qui voit tout. DNS, TCP/IP, SSH – ce ne sont pas des termes. Ce sont des pièges à intrusion, les remparts que j'ai forgés.

Les menaces ? Elles arrivent tous les jours. Botnets, scanners de ports, scripts automatiques. Ils viennent avec leurs intentions, mais se répartissent avec des erreurs 403 et des logs bien détaillés, parfois un petit cadeau empoisonné en guise d'avertissement. Tu veux tenter ta chance ? Sache que chaque porte que j'ai fermée est un avertissement en soi. J'anticipe, j'isole, j'exécute. Mon espace est sous contrôle.

Mon rôle n'est pas seulement de surveiller. C'est de maintenir une forteresse. Mon environnement, mes règles. Chaque jour, j'écris la loi. Bienvenue dans mon domaine numérique. J'espère que tu sais où tu mets les pieds. Car ici, seul le vrai sait naviguer. Le reste... se perd</strong></p>
    </div>

    <div class="profile-section">
    <h3>Compétences</h3>
    <ul>
        <li>💻 Développement Web : <strong>HTML, CSS, JavaScript, PHP, Python, SQL</strong></li>
        <li>🌐 Frameworks & Librairies : <strong>React, Angular, Vue.js, Django, Laravel</strong></li>
        <li>🖥️ Développement Backend : <strong>Node.js, Express, Django, Flask</strong></li>
        <li>📱 Développement Mobile : <strong>Flutter, React Native, Swift, Kotlin</strong></li>
        <li>💾 Bases de données : <strong>MySQL, PostgreSQL, MongoDB, Oracle, SQLite</strong></li>
        <li>☁️ Technologies Cloud : <strong>AWS, Google Cloud Platform, Microsoft Azure, Heroku, DigitalOcean</strong></li>
        <li>🔐 Cybersécurité : <strong>Pentest, OWASP, VPN, SSH, chiffrement AES/RSA, SSL/TLS, Pare-feu</strong></li>
        <li>🛠️ DevOps : <strong>Docker, Kubernetes, Jenkins, GitLab CI/CD, Ansible, Terraform</strong></li>
        <li>📈 Gestion de projet : <strong>Agile, Scrum, Kanban, Jira, Trello, Confluence</strong></li>
        <li>🧠 Intelligence Artificielle : <strong>Machine Learning, Deep Learning, TensorFlow, PyTorch</strong></li>
        <li>📊 Data Science & Analyse de données : <strong>Pandas, NumPy, SciPy, Matplotlib, SQL</strong></li>
        <li>🔍 Testing & Qualité : <strong>JUnit, Selenium, Mocha, Chai, Jest, Postman</strong></li>
        <li>⚙️ Automatisation : <strong>Python, Bash, PowerShell, scripts d'automatisation</strong></li>
        <li>🌍 Réseaux : <strong>TCP/IP, DNS, DHCP, VLAN, Subnetting, VPN, Proxy</strong></li>
        <li>📂 Gestion des Systèmes : <strong>Linux, Windows Server, Unix, Docker, Hyper-V</strong></li>
        <li>💬 Communication & Collaboration : <strong>Slack, Microsoft Teams, Zoom, Google Meet</strong></li>
        <li>📈 Analyse Business & SQL avancé : <strong>Business Intelligence, Tableau, Power BI</strong></li>
        <li>🎨 Design & Prototypage : <strong>Figma, Adobe XD, Sketch, Photoshop, Illustrator</strong></li>
    </ul>
</div>


    <!-- Bouton "Revenir en arrière" en bas -->
   
</div>

</body>
</html>
