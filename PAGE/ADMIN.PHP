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
$user_id = $_SESSION['user']['admin_id']; // Assurez-vous que l'ID de l'utilisateur est stocké ici
$sql = "SELECT Nom_famille, Prenom, Email, Photo_profil FROM administrateur WHERE admin_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom, $Email, $Photo_profil);
$stmt->fetch();
$stmt->close();

// Gérer le téléchargement de la photo de profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['Photo_profil'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["Photo_profil"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Vérifier si l'image est une vraie image
    $check = getimagesize($_FILES["Photo_profil"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['message'] = "Ce n'est pas une image.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier
    if ($_FILES["Photo_profil"]["size"] > 500000) {
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
        if (move_uploaded_file($_FILES["Photo_profil"]["tmp_name"], $target_file)) {
            // Mettre à jour le chemin de la photo de profil dans la base de données
            $sql = "UPDATE etudiant SET Photo_profil = ? WHERE etu_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Admin Dashboard</title>
    <link rel="stylesheet" href="#" />
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            
        }
        body {
            background-color: #f9f9fc; 
            color: #333;
            display: flex;
        }
        /* Sidebar Styling */
        .sidebar {
            background-color: #6a5acd;
            width: 250px;
            height: 100vh;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background 0.3s;
            cursor: pointer;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #5d55b1;
        }
        /* Dropdown Menu Styling */
        .dropdown {
            display: none;
            flex-direction: column;
            margin-left: 20px;
        }
        .dropdown a {
            background: #5d55b1;
            padding-left: 30px;
            transition: background 0.3s;
        }
        .dropdown a:hover {
            background: #4b45a0;
        }
        /* Show Dropdown Menu on Click */
        .show-dropdown {
            display: flex !important;
            position:relative;
            padding: 10px;
            bottom:350px;
            left:-10px;
            background-color: #fff;
           
            
            border-radius: 15px;
        }
 
        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background: linear-gradient(135deg, #7b13f1, #59098f, #180127);
            animation: gradientBackground 10s ease infinite;
        }
        @keyframes gradientBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        } 
        /* Display only active section */
        .main-content > div {
            display: none; /* Hide all sections by default */
        }
        .main-content > div.active {
            display: block; /* Show only the active section */
            
    background-color: rgb(255, 255, 255);
    background-color: rgba(255, 255, 255, 0.15);
    
    border-radius: 15px;
    backdrop-filter: blur(15px);
        }

        /* Dashboard Stats */
        .dashboard-stats {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
            
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(10px); }
        }
        .stat-card {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            background: linear-gradient(135deg, #12c2e9, #c471ed, #f64f59);
        }
        .stat-card:hover {
            transform: translateY(-50px);
        }
        .stat-card h3 {
            color: #6a5acd;
            font-size: 2em;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #555;
        }
        /* Sections like Hot Topics, Comment Moderation, etc. */
        .section {
            background: #fff;
            border-radius: 8px;
            padding: 60px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: auto;
        }

        .section1 {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            width: auto;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
        }

        overf{
        overflow-x: auto; /* Permet le défilement horizontal si le tableau est trop large */
            }
        h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
    .section2 {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        max-width: 100%; /* Assurez-vous que la section s'adapte au conteneur */
        overflow-x: auto; /* Permet le défilement horizontal si le tableau est trop large */
        border-radius: 15px;
    backdrop-filter: blur(15px);
    background-color: rgb(255, 255, 255);
    background-color: rgba(255, 255, 255, 0.15);
        
    }

    h3 {
        color: #333;
        margin-bottom: 15px;
    }

    table {
        width: 100%; /* S'assurer que le tableau occupe 100% de la largeur de son conteneur */
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        word-wrap: break-word; /* Permet le retour à la ligne pour le texte long */
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    img {
        border-radius: 50%;
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    button {
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }

    form {
        display: inline;
    }




        .section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .topic, .comment {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .topic:last-child, .comment:last-child {
            border-bottom: none;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .badge.active {
            background: #6a5acd;
            color: #fff;
        }
        .badge.locked {
            background: #e74c3c;
            color: #fff;
        }
        /* Buttons for Comment Moderation */
        .comment .btn {
            padding: 8px 12px;
            margin-left: 5px;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn.approve {
            background: #28a745;
        }
        .btn.remove {
            background: #e74c3c;
        }
        .btn.approve:hover {
            background: #218838;
        }
        .btn.remove:hover {
            background: #c0392b;
        }

        .container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 30px;
            
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: transform 0.6s;
        }

        .form-container:hover {
            transform: scale(1.02);
        }

        .logo {
            width: 120px;
            margin-bottom: 15px;
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

        h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2c5282;
        }

        p {
            color: #4a5568;
            margin-bottom: 20px;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        input[type="text"],
        input[type="Email"],
        input[type="tel"],
        input[type="date"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        input[type="Email"]:focus,
        input[type="tel"]:focus,
        input[type="date"]:focus,
        input[type="password"]:focus,
        select:focus {
            border: 1px solid #8f94fb;
            box-shadow: 0px 0px 6px rgba(142, 142, 255, 0.4);
            outline: none;
        }

        .password-info {
            font-size: 12px;
            color: #718096;
            margin-bottom: 10px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .checkbox-group label {
            margin-left: 5px;
        }

        .btn-submit {
            background: #4e54c8;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .btn-submit:hover {
            background: #8f94fb;
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        a {
            color: #4e54c8;
            text-decoration: none;
            
        }
        .btn-external{
            background: #07c911;
            color: white;
            padding: 12px;
            width: auto;
            border: none;
            border-radius: 18px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            font-size: x-large;
        }

        a:hover {
            text-decoration: underline;
        }
        .section3{
            background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        max-width: 100%; /* Assurez-vous que la section s'adapte au conteneur */
        overflow-x: auto; /* Permet le défilement horizontal si le tableau est trop large */
        text-align: center;
        background-color: rgb(255, 255, 255);
        background-color: rgba(255, 255, 255, 0.15);

        }
        .section3 h3 {
            color: #333;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fas fa-comments"></i> Forum Admin</h2>
        <a href="#" class="active" data-target="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="#" data-target="etudiant"><i class="fas fa-book"></i> Etudiant</a>
        <a href="#" data-target="enseignant"><i class="fas fa-flag"></i> Enseignants</a>
        <a href="#" data-target="entreprise"><i class="fas fa-users"></i> Entreprises</a>
        <a href="#" data-target="personnel"><i class="fas fa-layer-group"></i> Personnel</a>
        <a href="#" data-target="boite-reception"><i class="fas fa-ban"></i> Boîte de Réception</a>
        <!-- Settings Dropdown Menu -->
        <a href="#" id="settings-toggle"><i class="fas fa-cog"></i> Paramètre <i class="fas fa-chevron-down"></i></a>
        <div class="dropdown" id="settings-dropdown">
            <a href="menu.html">Accueil</a>
            <a href="charge.html">Mon profil</a>
            <a href="#">Notification</a>
            <a href="logout.html">Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Stats -->
        <div id="dashboard" class="section active">

                        <?php
                function countetu($conn) {
                    $sql = "SELECT COUNT(*) as total FROM etudiant"; 
                    $result = $conn->query($sql);

                    if ($result) {
                        $row = $result->fetch_assoc();
                        return $row['total'];
                    } else {
                        return 0; 
                    }
                }

                function countentre($conn) {
                    $sql = "SELECT COUNT(*) as total FROM entreprise"; 
                    $result = $conn->query($sql);

                    if ($result) {
                        $row = $result->fetch_assoc();
                        return $row['total'];
                    } else {
                        return 0; 
                    }
                }

                function countens($conn) {
                    $sql = "SELECT COUNT(*) as total FROM enseignant"; 
                    $result = $conn->query($sql);

                    if ($result) {
                        $row = $result->fetch_assoc();
                        return $row['total'];
                    } else {
                        return 0; 
                    }
                }

                function countadmin($conn) {
                    $sql = "SELECT COUNT(*) as total FROM personel"; 
                    $result = $conn->query($sql);

                    if ($result) {
                        $row = $result->fetch_assoc();
                        return $row['total'];
                    } else {
                        return 0; 
                    }
                }

                

                $totalUsers = countetu($conn);
                $totalens = countens($conn);
                $totalentre = countentre($conn);
                $totaladmin = countadmin($conn);
                
                ?>

                    <h3>TABLEAU DE STATISTIQUE</h3>
                    <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3><?php echo $totalentre; ?></h3>
                        <p>Total enteprises</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total étudiants</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalens; ?></h3>
                        <p>Total enseignant</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totaladmin; ?></h3>
                        <p>Total personel</p>
                    </div>
                </div>

                <!-- Hot Topics Section -->
                <div class="section">
                    <h3>SERVICES DU SITE</h3>
                    <div class="topic">
                        <span>DISCUSSION DANS LES FORUMS</span>
                        <span class="badge active">Active</span>
                    </div>
                    <div class="topic">
                        <span>INSCRIPTION ET CONNECTION AU SITE </span>
                        <span class="badge active">Active</span>
                    </div>
                    <div class="topic">
                        <span>LAISSER LES UTILISATEUR S'ECRIRENT ENTRE EUX</span>
                        <span class="badge locked">Locked</span>
                    </div>
            </div>

            <!-- Comment Moderation Section -->
            <div class="section">
                <h3>Moderation des publications</h3>
                <div class="comment">
                    <span>ESSOYA:  ce commentaire a été reporté</span>
                    <div>
                        <button class="btn approve">Approve</button>
                        <button class="btn remove">Remove</button>
                    </div>
                </div>
                <div class="comment">
                    <span>ZIGH: ce commentaire a été bloqué</span>
                    <div>
                        <button class="btn approve">Approve</button>
                        <button class="btn remove">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Etudiant Section -->
        <div id="etudiant" class="section">
         
                
                    <h3>Dashboard Stats</h3>
                    <div class="dashboard-stats">
                    <div class="stat-card">
                    <h3><?php echo $totalentre; ?></h3>
                    <p>Total enteprises</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total étudiants</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalens; ?></h3>
                    <p>Total enseignant</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totaladmin; ?></h3>
                    <p>Total personel</p>
                    </div>
                    </div>

                    <!-- Hot Topics Section -->
                    <div class="section2">
                        <h3>LISTE DES ETUDIANT INSCRITS SUR LA PLATFORME</h3>
                        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo de Profil</th>
                <th>Nom de Famille</th>
                <th>Prénom</th>
                <th>Nom d'Utilisateur</th>
                <th>Numéro de Téléphone</th>
                <th>Genre</th>
                <th>Email</th>
                <th>Date de Naissance</th>
                <th>Mot de Passe</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            $conn = new mysqli($host, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Échec de la connexion: " . $conn->connect_error);
            }

           
            $sql = "SELECT * FROM etudiant";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['etu_id']}</td>
                            <td><img src='{$row['Photo_profil']}' alt='Photo de {$row['Prenom']}' style='width: 50px; height: 50px; border-radius: 50%;'></td>
                            <td>{$row['Nom_famille']}</td>
                            <td>{$row['Prenom']}</td>
                            <td>{$row['Téléphone']}</td>
                            <td>{$row['Genre']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['DATE_NAISSANCE']}</td>
                            <td>••••••••••••</td>
                            <td>{$row['etu_type']}</td>
                            <td>
                                <form action='modifier_etudiant.php' method='GET' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['etu_id']}'>
                                    <button type='submit'>Modifier</button>
                                </form>
                                <form action='supprimer_etudiant.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['etu_id']}'>
                                    <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet étudiant ?\");'>Supprimer</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>Aucun étudiant trouvé.</td></tr>";
            }

            
            ?>
        </tbody>
    </table> 
                   
                    </div>

                    <!-- Comment Moderation Section -->
                    <div class="section1">
                        <H3>INSCRIVEZ UN NOUVEL ETUDIANT</H3>
                    <form id="registrationForm" action="REGISAD.php" method="POST" class="sign-up-form">
                <div class="input-group">
                    <input type="text" id="prenom" name="prenom"  placeholder="Prénom" required>
                    <input type="text" id="famille" name="famille" placeholder="Nom de famille" required>
                </div>
                <input type="Email" id="Email" name="Email" placeholder="Addresse Email" required>
                <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                <input type="date" id="dob" name="dob" placeholder="Date de Naissance" required>
                <div class="input-group">
                    <select id="gender" name="gender" required>
                        <option value="">GENRE</option>
                        <option value="Male">HOMME</option>
                        <option value="Female">FEMME</option>
                        <option value="Other">WOUBI</option>
                    </select>
                    <select id="role" name="role" required>
                        
                        <option value="ETUDIANT">ETUDIANT</option>
                       
                    </select>
                </div>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
                <div class="checkbox-group">
                 
                </div>
                <button type="submit" class="btn-submit">Créer un compte</button>
                
            </form>
                    
                    </div>
                    <div class="section3">
                        <h3>Dans la peau d'un étudiant</h3>
                        <a href="login.php" target="_blank" class="external-link">
                            <button class="btn-external">se connecter en étudiant</button>
                        </a> <br>

                        <DIV> <br><a href="DASHADMIN.PHP" target="_blank" class="external-link">
                            <button class="btn-external">Voir le forum des étudiants</button>
                        </a></DIV>

                        
                        
                    
                    </div>
        </div>


        <!-- Enseignants Section -->
        <div id="enseignant" class="section">
            <h3>TABLEAU DE BORD DES ENSEIGNANTS</h3>
           
                    <div class="dashboard-stats">
                    <div class="stat-card">
                    <h3><?php echo $totalentre; ?></h3>
                    <p>Total enteprises</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total étudiants</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalens; ?></h3>
                    <p>Total enseignant</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totaladmin; ?></h3>
                    <p>Total personel</p>
                    </div>
                    </div>

                    <!-- Hot Topics Section -->
                    <div class="section2">
                        <h3>LISTE DES ENSEIGNANTS INSCRITS SUR LA PLATFORME</h3>
                        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo de Profil</th>
                <th>Nom de Famille</th>
                <th>Prénom</th>
                <th>Nom d'Utilisateur</th>
                <th>Numéro de Téléphone</th>
                <th>Genre</th>
                <th>Email</th>
                <th>Date de Naissance</th>
                <th>Mot de Passe</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            $conn = new mysqli($host, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Échec de la connexion: " . $conn->connect_error);
            }

           
            $sql = "SELECT * FROM enseignant";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['ens_id']}</td>
                            <td><img src='{$row['Photo_profil']}' alt='Photo de {$row['Prenom']}' style='width: 50px; height: 50px; border-radius: 50%;'></td>
                            <td>{$row['Nom_famille']}</td>
                            <td>{$row['Prenom']}</td>
                            <td>{$row['Téléphone']}</td>
                            <td>{$row['Genre']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['DATE_NAISSANCE']}</td>
                            <td>••••••••••••</td>
                            <td>{$row['ens_type']}</td>
                            <td>
                                <form action='modifier_enseignant.php' method='GET' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['ens_id']}'>
                                    <button type='submit'>Modifier</button>
                                </form>
                                <form action='supprimer_enseignant.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['ens_id']}'>
                                    <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet enseignant ?\");'>Supprimer</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>Aucun étudiant trouvé.</td></tr>";
            }

            
            ?>
        </tbody>
    </table>
                   
                    </div>

                    <!-- Comment Moderation Section -->
                    <div class="section1">
                        <H3>INSCRIVEZ UN NOUVEL ENSEIGNANT</H3>
                    <form id="registrationForm" action="REGISAD.php" method="POST" class="sign-up-form">
                <div class="input-group">
                    <input type="text" id="prenom" name="prenom"  placeholder="Prénom" required>
                    <input type="text" id="famille" name="famille" placeholder="Nom de famille" required>
                </div>
                <input type="Email" id="Email" name="Email" placeholder="Addresse Email" required>
                <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                <input type="date" id="dob" name="dob" placeholder="Date de Naissance" required>
                <div class="input-group">
                    <select id="gender" name="gender" required>
                        <option value="">GENRE</option>
                        <option value="Male">HOMME</option>
                        <option value="Female">FEMME</option>
                        <option value="Other">WOUBI</option>
                    </select>
                    <select id="role" name="role" required>
                    
                        <option value="ENSEIGNANT">ENSEIGNANT</option>
                    
                    </select>
                </div>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
                <div class="checkbox-group">
                 
                </div>
                <button type="submit" class="btn-submit">Créer un compte</button>
                
            </form>
                    
                    </div>
                    <div class="section3">
                        <h3>Dans la peau d'un étudiant</h3>
                        <a href="login.php" target="_blank" class="external-link">
                            <button class="btn-external">se connecter en enseignant</button>
                        </a> <br>

                        <DIV> <br><a href="DASHADMIN.PHP" target="_blank" class="external-link">
                            <button class="btn-external">Voir le forum des enseigants</button>
                        </a></DIV>

                        
                        
                    
                    </div>
        </div>

        <!-- Entreprises Section -->
        <div id="entreprise" class="section">
            <h3>TABLEAU DE BORD DES ENTREPRISES</h3>
                  
            
                    <div class="dashboard-stats">
                    <div class="stat-card">
                    <h3><?php echo $totalentre; ?></h3>
                    <p>Total enteprises</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total étudiants</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalens; ?></h3>
                    <p>Total enseignant</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totaladmin; ?></h3>
                    <p>Total personel</p>
                    </div>
                    </div>

                    <!-- Hot Topics Section -->
                    <div class="section2">
                        <h3>LISTE DES ENTREPRISES INSCRITES SUR LA PLATFORME</h3>
                        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo de Profil</th>
                <th>Nom de Famille</th>
                <th>Prénom</th>
                <th>Nom d'Utilisateur</th>
                <th>Numéro de Téléphone</th>
                <th>Genre</th>
                <th>Email</th>
                <th>Date de Naissance</th>
                <th>Mot de Passe</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            $conn = new mysqli($host, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Échec de la connexion: " . $conn->connect_error);
            }

           
            $sql = "SELECT * FROM entreprise";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['entre_id']}</td>
                            <td><img src='{$row['Photo_profil']}' alt='Photo de {$row['Prenom']}' style='width: 50px; height: 50px; border-radius: 50%;'></td>
                            <td>{$row['Nom_famille']}</td>
                            <td>{$row['Prenom']}</td>
                            <td>{$row['Téléphone']}</td>
                            <td>{$row['Genre']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['DATE_NAISSANCE']}</td>
                            <td>••••••••••••</td>
                            <td>{$row['entre_type']}</td>
                            <td>
                                <form action='modifier_entreprise.php' method='GET' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['entre_id']}'>
                                    <button type='submit'>Modifier</button>
                                </form>
                                <form action='supprimer_entreprise.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['entre_id']}'>
                                    <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette entreprise ?\");'>Supprimer</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>Aucun étudiant trouvé.</td></tr>";
            }

            
            ?>
        </tbody>
    </table>
                   
                    </div>

                    <!-- Comment Moderation Section -->
                    <div class="section1">
                        <H3>INSCRIVEZ UN NOUVEL ETUDIANT</H3>
                    <form id="registrationForm" action="REGISAD.php" method="POST" class="sign-up-form">
                <div class="input-group">
                    <input type="text" id="prenom" name="prenom"  placeholder="Prénom" required>
                    <input type="text" id="famille" name="famille" placeholder="Nom de famille" required>
                </div>
                <input type="Email" id="Email" name="Email" placeholder="Addresse Email" required>
                <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                <input type="date" id="dob" name="dob" placeholder="Date de Naissance" required>
                <div class="input-group">
                    <select id="gender" name="gender" required>
                        <option value="">GENRE</option>
                        <option value="Male">HOMME</option>
                        <option value="Female">FEMME</option>
                        <option value="Other">AUTRES</option>
                    </select>
                    <select id="role" name="role" required>
                        
                       
                        <option value="ENTREPRISE">ENTREPRISE</option>
                    </select>
                </div>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
                <div class="checkbox-group">
                 
                </div>
                <button type="submit" class="btn-submit">Créer un compte</button>
                
            </form>
                    
                    </div>
                    <div class="section3">
                        <h3>Dans la peau d'une entreprise</h3>
                        <a href="login.php" target="_blank" class="external-link">
                            <button class="btn-external">se connecter en entrepise</button>
                        </a> <br>

                        <DIV> <br><a href="DASHADMIN.PHP" target="_blank" class="external-link">
                            <button class="btn-external">Voir le forum des entreprise</button>
                        </a></DIV>

                        
                        
                    
                    </div>
        </div>

        <!-- Personnel Section -->
        <div id="personnel" class="section">
            <h3>Personnel</h3>
            
            
                
            <h3>Dashboard Stats</h3>
                    <div class="dashboard-stats">
                    <div class="stat-card">
                    <h3><?php echo $totalentre; ?></h3>
                    <p>Total enteprises</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total étudiants</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totalens; ?></h3>
                    <p>Total enseignant</p>
                    </div>
                    <div class="stat-card">
                    <h3><?php echo $totaladmin; ?></h3>
                    <p>Total personel</p>
                    </div>
                    </div>

                    <!-- Hot Topics Section -->
                    <div class="section2">
                        <h3>LISTE DE PERSONEL INSCRITS SUR LA PLATFORME</h3>
                        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo de Profil</th>
                <th>Nom de Famille</th>
                <th>Prénom</th>
                <th>Nom d'Utilisateur</th>
                <th>Numéro de Téléphone</th>
                <th>Genre</th>
                <th>Email</th>
                <th>Date de Naissance</th>
                <th>Mot de Passe</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            $conn = new mysqli($host, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Échec de la connexion: " . $conn->connect_error);
            }

           
            $sql = "SELECT * FROM personel";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['pers_id']}</td>
                            <td><img src='{$row['Photo_profil']}' alt='Photo de {$row['Prenom']}' style='width: 50px; height: 50px; border-radius: 50%;'></td>
                            <td>{$row['Nom_famille']}</td>
                            <td>{$row['Prenom']}</td>
                            <td>{$row['Téléphone']}</td>
                            <td>{$row['Genre']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['DATE_NAISSANCE']}</td>
                            <td>••••••••••••</td>
                            <td>{$row['pers_type']}</td>
                            <td>
                                <form action='modifier_personel.php' method='GET' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['pers_id']}'>
                                    <button type='submit'>Modifier</button>
                                </form>
                                <form action='supprimer_personel.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['pers_id']}'>
                                    <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce personel ?\");'>Supprimer</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>Aucun personel trouvé.</td></tr>";
            }

            
            ?>
        </tbody>
    </table> 
                   
                    </div>

                    <!-- Comment Moderation Section -->
                    <div class="section1">
                        <H3>INSCRIVEZ UN NOUVEL PERSONEL</H3>
                    <form id="registrationForm" action="REGISAD.php" method="POST" class="sign-up-form">
                <div class="input-group">
                    <input type="text" id="prenom" name="prenom"  placeholder="Prénom" required>
                    <input type="text" id="famille" name="famille" placeholder="Nom de famille" required>
                </div>
                <input type="Email" id="Email" name="Email" placeholder="Addresse Email" required>
                <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                <input type="date" id="dob" name="dob" placeholder="Date de Naissance" required>
                <div class="input-group">
                    <select id="gender" name="gender" required>
                        <option value="">GENRE</option>
                        <option value="Male">HOMME</option>
                        <option value="Female">FEMME</option>
                        <option value="Other">WOUBI</option>
                    </select>
                    <select id="role" name="role" required>
                        
                        <option value="PERSONEL">PERSONEL</option>
                       
                    </select>
                </div>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
                <div class="checkbox-group">
                 
                </div>
                <button type="submit" class="btn-submit">Créer un compte</button>
                
            </form>
                    
                    </div>
                    <div class="section3">
                        <h3>Dans la peau d'un étudiant</h3>
                        <a href="login.php" target="_blank" class="external-link">
                            <button class="btn-external">se connecter en étudiant</button>
                        </a> <br>

                        <DIV> <br><a href="DASHADMIN.PHP" target="_blank" class="external-link">
                            <button class="btn-external">Voir le forum des étudiants</button>
                        </a></DIV>

                        
                        
                    
                    </div>
        </div>

        <!-- Boîte de Réception Section -->
        <div id="boite-reception" class="section">
            <h3>Boîte de Réception</h3>
            <p>Messages reçus ici.</p>
        </div>

        <!-- Comment Moderation Section -->
        <div class="section">
            <h3>Comment Moderation</h3>
            <div class="comment">
                <span>User123: This comment has been reported</span>
                <div>
                    <button class="btn approve">Approve</button>
                    <button class="btn remove">Remove</button>
                </div>
            </div>
            <div class="comment">
                <span>User456: This comment has been flagged</span>
                <div>
                    <button class="btn approve">Approve</button>
                    <button class="btn remove">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Toggle Dropdown Menu and Section Display -->
    <script>
        document.getElementById('settings-toggle').addEventListener('click', function (event) {
            event.preventDefault();
            document.getElementById('settings-dropdown').classList.toggle('show-dropdown');
        });

        const links = document.querySelectorAll('.sidebar a[data-target]');
        links.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                links.forEach(l => l.classList.remove('active')); // Remove active class from all links
                this.classList.add('active'); // Add active class to the clicked link

                const targetId = this.getAttribute('data-target');
                const sections = document.querySelectorAll('.main-content > div');
                sections.forEach(section => {
                    section.classList.remove('active'); // Hide all sections
                });
                document.getElementById(targetId).classList.add('active'); // Show the target section
            });
        });
    </script>
</body>
</html>




<?php
$conn->close();
?>
