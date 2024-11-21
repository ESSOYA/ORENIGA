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
$sql = "SELECT Nom_famille, Prenom,Type_entre,DATE_NAISSANCE, Email, Photo_profil, Mot_de_passe,entre_type,Téléphone,Bio,Competence_info,Competence_marketing,competence_digital FROM entreprise WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom,$TYPO,$DATE, $email, $photo_profil, $pass,$type,$phone,$bio,$info,$marketing,$digital );
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
            $sql = "UPDATE entreprise  SET Photo_profil = ? WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $BIRTH = $conn->real_escape_string($_POST['BIRTH']);
   
        $sql = "UPDATE entreprise  SET DATE_NAISSANCE = ? WHERE entre_id = ?"; // Assurez-vous que 'user_id' correspond au champ de votre table
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $BIRTH, $user_id);
        $stmt->execute();
        $_SESSION['message'] = "La DATE DE NAISSANCE  a été modifiée avec succès.";

    // Rediriger pour éviter la soumission répétée du formulaire
    header("Location: entreprise.php");
    exit();
}

?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Entreprise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

:root {
    --primary-color: #4a90e2;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --success-color: #2ecc71;
    --background-color: #f5f5f5;
}

body {
    background-color: var(--background-color);
    display: flex;
    min-height: 100vh;
    flex-direction: column;
}

.sidebar {
    width: 280px;
    background: linear-gradient(180deg, var(--secondary-color) 0%, #1a2634 100%);
    height: 100vh;
    padding: 20px 0;
    position: fixed;
    color: white;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid var(--primary-color);
    padding: 3px;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.image:hover {
    transform: scale(1.05);
}

.sidebar-menu {
    padding: 20px;
}

.menu-item {
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    border-radius: 12px;
    position: relative;
}

.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.menu-item.active {
    background: linear-gradient(45deg, var(--primary-color), #2980b9);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.menu-item i {
    margin-right: 12px;
    width: 20px;
    transition: transform 0.3s ease;
}

.menu-item:hover i {
    transform: scale(1.2);
}

.main-content {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    transition: margin-left 0.3s ease;
}

.content-section {
    display: none;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s ease;
}

.content-section.active {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.dashboard-header {
    background: linear-gradient(45deg, #fff, #f8f9fa);
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.dashboard-header h1 {
    color: var(--secondary-color);
    font-size: 28px;
    font-weight: 600;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    color: #7f8c8d;
    font-size: 1em;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.stat-card h3 i {
    margin-right: 10px;
    color: var(--primary-color);
}

.stat-card .value {
    font-size: 2.2em;
    color: var(--secondary-color);
    font-weight: 600;
}

.form-group {
    margin-bottom: 25px;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 500;
    color: var(--secondary-color);
}

.form-group input[type="text"],
.form-group input[type="date"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 1em;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.btn {
    padding: 12px 30px;
    background: linear-gradient(45deg, var(--primary-color), #2980b9);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 1em;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn-danger {
    background: linear-gradient(45deg, var(--accent-color), #c0392b);
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.notification-item {
    padding: 15px;
    border-radius: 10px;
    background-color: #f8f9fa;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #e9ecef;
}

.notification-icon {
    margin-right: 15px;
    color: var(--primary-color);
}

.theme-selector {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.theme-option {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.theme-option:hover {
    transform: scale(1.1);
}

.theme-option.active {
    border-color: var(--primary-color);
}

/* Media Queries pour la responsivité */

/* Pour les écrans de taille moyenne (tablettes) */
@media (max-width: 768px) {
    .sidebar {
        width: 240px;
    }
    
    .main-content {
        margin-left: 240px;
        width: calc(100% - 240px);
    }

    .stats-container {
        grid-template-columns: 1fr 1fr;
    }

    .stat-card {
        padding: 20px;
    }

    .form-group {
        padding: 20px;
    }
}

/* Pour les écrans plus petits (smartphones) */
@media (max-width: 480px) {
    .sidebar {
        width: 100%;
        position: relative;
        height: auto;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .menu-item {
        padding: 12px;
    }

    .stats-container {
        grid-template-columns: 1fr;
    }

    .dashboard-header h1 {
        font-size: 22px;
    }

    .stat-card h3 {
        font-size: 0.9em;
    }
}


   </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo htmlspecialchars($photo_profil); ?>" alt="Profile" class="image" id="image">
            <script>
            const img = document.getElementById('image');
            img.addEventListener('error', function() {
                img.src = "404.jpg"; 
                img.alt = "Image alternative"; 
            });
        </script>
            <h2><?php echo htmlspecialchars($nom); ?>   <?php echo htmlspecialchars($prenom); ?></h2>
            <h3 style="color: #a0aec0;  font-size: x-large;"><?php echo htmlspecialchars($type); ?></h3>
            
            <p style="color: #ff006a;font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif; margin-top: 5px;"><?php echo htmlspecialchars($TYPO); ?></p>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-section="dashboard">
                <i class="fas fa-home"></i>
                Tableau de bord
            </div>
            <div class="menu-item" data-section="profile">
                <i class="fas fa-user"></i>
                Profil
            </div>
            <div class="menu-item" data-section="security">
                <i class="fas fa-lock"></i>
                Sécurité
            </div>
            <div class="menu-item" data-section="notifications">
                <i class="fas fa-bell"></i>
                Notifications
            </div>
            <div class="menu-item" data-section="preferences">
                <i class="fas fa-cog"></i>
                Préférences
            </div>
            <div class="menu-item" data-section="billing">
            <i class="fas fa-building"></i>
                ESPACE <?php echo htmlspecialchars($type); ?>
            </div>
            <div class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </div>
        </div>
    </div>

    <div class="main-content">
        <div id="dashboard" class="content-section active">
            <div class="dashboard-header">
                <h1>Tableau de bord</h1>
            </div>
            <div class="stats-container">
                <div class="stat-card">
                    <h3><i class="fas fa-envelope"></i>Messages</h3>
                    <div class="value">5</div>
                    <button type="button" class="btn" onclick="window.location.href='message.php';">Accéder au message</button>
                    <button type="button" class="btn" onclick="window.location.href='charge_entreprise.html';">acceder aux discussion</button>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-bell"></i>Notifications</h3>
                    <div class="value">3</div>
                     

                </div>
            </div>
        
            <div class="form-group">
                <label>Authentification à deux facteurs</label>
               <h2>vos informations s'afficheront ici</h2>
            </div>

        </div>

        <div id="profile" class="content-section">
            <div class="dashboard-header">
                <h1>Mon Profil</h1>
            </div>

            <form action="IMAGE_ENTRE.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
    <label>Photo de profil</label>
   <br><br>
    <label for="photo_profil" class="btn1">
        <i class="fa fa-upload"></i> Télécharger une photo
    </label>
    <input type="file" name="photo_profil" id="photo_profil" accept=".jpg,.jpeg,.png" required style="display: none;" onchange="previewImage();">
    <div id="imagePreviewContainer" style="display:none;">
        <h4>Prévisualisation :</h4>
        <img id="imagePreview" src="" alt="Prévisualisation" style="max-width: 200px; border: 1px solid #ccc; margin-top: 10px;">
    </div>
    <script>
    
    function previewImage() {
        const file = document.getElementById('photo_profil').files[0];
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImage = document.getElementById('imagePreview');
        
        if (file) {
            
            const reader = new FileReader();
            
            reader.onload = function(event) {
                previewImage.src = event.target.result;
                previewContainer.style.display = 'block'; 
            }
            
            reader.readAsDataURL(file); 
        } else {
            previewContainer.style.display = 'none'; 
        }
    }
</script>


    <br><br>
    
    <button type="submit" class="btn">Mettre à jour la photo de profil</button>
    <br><br><br>
    
    <label>Voulez-vous supprimer votre photo de profil ?</label>
    <!-- Bouton pour supprimer la photo avec une icône Font Awesome -->
    <button type="button" class="btn" onclick="window.location.href='su_pp_entre.php';">
        <i class="fa fa-trash"></i> Supprimer la photo de profil
    </button>
</div>

</form>

            <form action="MODentre.PHP" method="POST" >
            
            <div class="form-group">
                <label>Nom</label>
                <input type="text" value="<?php echo htmlspecialchars($nom); ?>" name="famille" >
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" value="<?php echo htmlspecialchars($prenom); ?>" name="prenom">
            </div>
            <div class="form-group">
                <label>TYPE D'ENTREPRISE</label>
                <input type="text" value="<?php echo htmlspecialchars($TYPO); ?>" name="TYPO" >
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($email); ?>" name="email">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" value="<?php echo htmlspecialchars($phone); ?>" name="phone">
            </div>
            <div class="form-group">
                <label>Date de Naissance</label>
                <input type="date" value="<?php echo htmlspecialchars($DATE); ?>" name="dob">
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea rows="4" value="<?php echo htmlspecialchars($bio); ?>"  name="bio"><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
            <div class="form-group">
                <label>👨‍💻Compétences en informatique</label>
                <textarea rows="2" value="<?php echo htmlspecialchars($info); ?>" name="info"><?php echo htmlspecialchars($info); ?></textarea>
            </div>
            <div class="form-group">
                <label>💡Compétences en marketing </label>
                <textarea rows="2" value="<?php echo htmlspecialchars($marketing); ?>" name="marketing"><?php echo htmlspecialchars($marketing); ?></textarea>
            </div>
            <div class="form-group">
                <label>🌍Compétences digtales </label>
                <textarea rows="2" value="<?php echo htmlspecialchars($digital); ?>"  name="digital"><?php echo htmlspecialchars($digital); ?></textarea>
            </div>
            <button class="btn">Sauvegarder les modifications</button>
            </form> 
        </div>

        <div id="security" class="content-section">
            <div class="dashboard-header">
                <h1>Sécurité</h1>
            </div>
            <form action="SECURITE_entre.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePassword()">
    <div class="form-group">
        <label>Ancien mot de passe</label>
        <input type="password" id="oldPassword" name="oldPassword" required>
    </div>
    <div class="form-group">
        <label>Nouveau mot de passe</label>
        <input type="password" id="newPassword" name="newPassword" required>
    </div>
    <div class="form-group">
        <label>Confirmer le mot de passe</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>
    </div>

    <div class="form-group">
        <input type="checkbox" id="showPassword" onclick="togglePasswordVisibility()">
        <label for="showPassword">Afficher le mot de passe</label>
    </div>
    
    <button class="btn" type="submit">Mettre à jour la sécurité</button>
</form>

<script>
    function validatePassword() {
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword === oldPassword) {
            alert("Le nouveau mot de passe doit être différent de l'ancien mot de passe.");
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            alert("Le nouveau mot de passe et la confirmation ne correspondent pas.");
            return false;
        }

        return true;
    }

    function togglePasswordVisibility() {
        const oldPassword = document.getElementById('oldPassword');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const type = oldPassword.type === "password" ? "text" : "password";

        oldPassword.type = type;
        newPassword.type = type;
        confirmPassword.type = type;
    }
</script>

        </div>

        <div id="notifications" class="content-section">
           <section id="notifications" class="notifications">
           <div class="dashboard-header">
                <h1>Notifications</h1>
            </div>
            <div class="form-group">
                <h3>Notifications récentes</h3>
                
                <div class="notification-item">
                    <i class="fas fa-envelope notification-icon"></i>
                    <div>Nouveau message</div>
                </div>
            </div>
            <div class="form-group">
                <h3>Paramètres de notification</h3>
                
            </div>
            <button class="btn">Sauvegarder les préférences</button>
        </div>

        <div id="preferences" class="content-section">
            <div class="dashboard-header">
                <h1>Préférences</h1>
            </div>
            <div class="form-group">
                <label>Langue</label>
                <div class="theme-switcher">
                <button class="btn" onclick="toggleTheme()">
                    <i class="fas fa-paint-brush"></i>
                </button>
            </div>

            </div>
            <div class="form-group">
                <label>Thème</label>
                <div class="theme-selector">
                    <div class="theme-option active" style="background-color: #4a90e2;"></div>
                    <div class="theme-option" style="background-color: #2ecc71;"></div>
                    <div class="theme-option" style="background-color: #e74c3c;"></div>
                    <div class="theme-option" style="background-color: #9b59b6;"></div>
                </div>
            </div>
           </section>
            
        </div>

        <div id="billing" class="content-section">
            <div class="dashboard-header">
                <h1>Votre espace à vous !</h1>
            </div>
            <div class="form-group">
                <h3>Méthode de paiement</h3>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button class="btn">Ajouter une carte</button>
                    <button class="btn">Gérer les paiements</button>
                </div>
            </div>
            <div class="form-group">
                <h3>Historique des factures</h3>
                <table style="width: 100%; margin-top: 15px;">
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                    <tr>
                        <td>01/06/2023</td>
                        <td>250€</td>
                        <td>Payé</td>
                    </tr>
                    <tr>
                        <td>01/05/2023</td>
                        <td>250€</td>
                        <td>Payé</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
    // Menu navigation functionality
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            // Handle logout confirmation and redirection
            if (this.querySelector('i').classList.contains('fa-sign-out-alt')) {
                if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
                    
                    window.location.href = "logout.html";
                }
                return;
            }

            // Remove 'active' class from all menu items and sections
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
            });

            // Add 'active' class to clicked menu item and its target section
            this.classList.add('active');
            const sectionId = this.getAttribute('data-section');
            const targetSection = document.getElementById(sectionId);
            targetSection.classList.add('active');
            void targetSection.offsetWidth; // Trigger reflow for animation

            targetSection.style.opacity = '1';
            targetSection.style.transform = 'translateY(0)';
        });
    });

    // Theme selector functionality
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            document.documentElement.style.setProperty('--primary-color', this.style.backgroundColor);
        });
    });

    // Show login notification and update last login time on page load
    window.onload = function() {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Update last login timestamp
        const now = new Date();
        const options = {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const lastLogin = document.getElementById('last-login');
        if (lastLogin) {
            lastLogin.textContent = now.toLocaleDateString('fr-FR', options);
        }
    };
    function toggleTheme() {
            darkMode = !darkMode;
            document.documentElement.style.setProperty('--background-color', darkMode ? '#1a1a1a' : '#f5f5f5');
            document.documentElement.style.setProperty('--primary-color', darkMode ? '#66bb6a' : '#4CAF50');
            showNotification(darkMode ? 'Mode sombre activé' : 'Mode clair activé');
        }
        toggleTheme();
</script>

</body>
</html>