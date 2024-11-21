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

// Vérifier si l'ID de l'utilisateur est passé en paramètre
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM enseignant WHERE ens_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Utilisateur non trouvé.");
    }
} else {
    die("ID de l'utilisateur non fourni.");
}

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $famille = $conn->real_escape_string($_POST['famille']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hachage du mot de passe

    // Gérer le téléchargement de la photo de profil
    $photoProfil = $user['photo_profil']; // Par défaut, garder l'ancienne photo
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "PROFIL/"; // Répertoire pour stocker les photos
        $targetFile = $targetDir . basename($_FILES['photo_profil']['name']);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Vérifier le type de fichier
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $targetFile)) {
                $photoProfil = $targetFile; // Mettre à jour le chemin de la photo de profil
            } else {
                echo "<p>Erreur lors du téléchargement de la photo de profil.</p>";
            }
        } else {
            echo "<p>Seules les images JPG, JPEG, PNG et GIF sont autorisées.</p>";
        }
    }

    // Mettre à jour les informations de l'utilisateur
    $updateSql = "UPDATE enseignant SET Prenom=?, Nom_famille=?,  Email=?, Téléphone=?, DATE_NAISSANCE=?, Genre=?, ens_type=?, Mot_de_passe=?, Photo_profil=? WHERE ens_id=?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssssssssi", $prenom, $famille, $email, $phone, $dob, $gender, $role, $password, $photoProfil, $userId);

    if ($updateStmt->execute()) {
        echo "<p>Informations mises à jour avec succès.</p>";
        header("Location: admin.php");
    } else {
        echo "<p>Erreur lors de la mise à jour des informations: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
    <link rel="stylesheet" href="style.css"> <!-- Si vous avez un fichier CSS externe -->
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4; /* Couleur de fond de la page */
    margin: 0;
    padding: 20px;
    min-height: 100vh;
            background: linear-gradient(135deg, #3b002f, #0d014e);
    
}

.sign-up-form {
    background-color: #fff; /* Couleur de fond du formulaire */
    border-radius: 18px; /* Coins arrondis */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Ombre du formulaire */
    max-width: 700px; /* Largeur maximale du formulaire */
    min-height: 400px;
    margin: 0 auto; /* Centrer le formulaire */
    padding: 20px; /* Espacement intérieur */
    margin-TOP: 100px;
    background-color: rgb(255, 255, 255);
    background-color: rgba(255, 255, 255, 0.15);
   
    backdrop-filter: blur(15px);
}

h3 {
    text-align: center; /* Centrer le titre */
    color: #333; /* Couleur du texte */
}

.input-group {
    display: flex; /* Utiliser le flexbox pour les groupes d'inputs */
    justify-content: space-between; /* Espacement entre les éléments */
    margin-bottom: 15px; /* Espacement entre les groupes */
}

.input-group input,
.input-group select {
    flex: 1; /* Prendre tout l'espace disponible */
    padding: 10px; /* Espacement intérieur */
    margin-right: 10px; /* Espacement entre les champs */
    border: 1px solid #ccc; /* Bordure des champs */
    border-radius: 4px; /* Coins arrondis */
    transition: border-color 0.3s; /* Transition de couleur */
}

.input-group input:focus,
.input-group select:focus {
    border-color: #007bff; /* Couleur de bordure lors du focus */
    outline: none; /* Enlever le contour par défaut */
}

.input-group input[type="file"] {
    padding: 0; /* Enlever le padding pour le champ fichier */
}

.btn-submit {
    background-color: #007bff; /* Couleur de fond du bouton */
    color: white; /* Couleur du texte du bouton */
    padding: 10px 15px; /* Espacement intérieur du bouton */
    border: none; /* Pas de bordure */
    border-radius: 4px; /* Coins arrondis */
    cursor: pointer; /* Changer le curseur */
    width: 100%; /* Prendre toute la largeur */
    transition: background-color 0.3s; /* Transition de couleur */
}

.btn-submit:hover {
    background-color: #0056b3; /* Couleur au survol */
}

.password-info {
    font-size: 0.9em; /* Taille de police plus petite */
    color: #666; /* Couleur de texte plus claire */
    margin: 10px 0; /* Espacement vertical */
}

.checkbox-group {
    margin: 15px 0; /* Espacement vertical */
}

.checkbox-group label {
    font-size: 0.9em; /* Taille de police pour les labels */
}

#imagePreview {
    display: none; /* Masquer l'aperçu par défaut */
    margin: 10px 0; /* Espacement vertical */
    border: 1px solid #ccc; /* Bordure de l'aperçu */
    border-radius: 50%; /* Arrondi de l'aperçu */
    object-fit: cover; /* Couvrir tout l'espace */
}

@media (max-width: 600px) {
    .input-group {
        flex-direction: column; /* Empiler les champs sur petits écrans */
    }
    
    .input-group input,
    .input-group select {
        margin-right: 0; /* Enlever le margine droit */
        margin-bottom: 10px; /* Espacement vertical */
    }
}
.btn-link {
    display: inline-block; /* Pour que le lien se comporte comme un bloc */
    background-color: #28a745; /* Couleur de fond du bouton */
    color: white; /* Couleur du texte du bouton */
    padding: 10px 1px; /* Espacement intérieur du bouton */
    border: none; /* Pas de bordure */
    border-radius: 4px; /* Coins arrondis */
    text-decoration: none; /* Enlever le soulignement */
    text-align: center; /* Centrer le texte */
    width: 100%; /* Prendre toute la largeur */
    transition: background-color 0.3s; /* Transition de couleur */
    margin-top: 10px; /* Espacement supérieur */
}

.btn-link:hover {
    background-color: #218838; /* Couleur au survol */
}

.floating-logo {
            width: 135px;
            margin-bottom: 1rem;
            margin:0;
            text-align: center; 
            
            animation: float 1s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(10); }
            50% { transform: translateY(-20px); }
        }


    </style>
</head>
<body>

<form id="registrationForm" action="" method="POST" enctype="multipart/form-data" class="sign-up-form">
<img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="floating-logo">
<img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="floating-logo">
<img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="floating-logo">
<img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="floating-logo">
<img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="floating-logo">

    <div class="input-group">
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['Prenom']); ?>" placeholder="Prénom" >
        <input type="text" id="famille" name="famille" value="<?php echo htmlspecialchars($user['Nom_famille']); ?>" placeholder="Nom de famille" >
    </div>
   
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" placeholder="Adresse email" >
    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Téléphone']); ?>" placeholder="Téléphone" >
    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['DATE_NAISSANCE']); ?>" placeholder="Date de Naissance" >
    <div class="input-group">
        <select id="gender" name="gender" required>
            <option value="">GENRE</option>
            <option value="Male" <?php echo ($user['Genre'] === 'HOMME') ? 'selected' : ''; ?>>HOMME</option>
            <option value="Female" <?php echo ($user['Genre'] === 'FEMME') ? 'selected' : ''; ?>>FEMME</option>
            <option value="Other" <?php echo ($user['Genre'] === 'AUTRE') ? 'selected' : ''; ?>>AUTRE</option>
        </select>
        <select id="role" name="role" >
            
            <option value="ENSEIGNANT" <?php echo ($user['ens_type'] === 'ENSEIGNANT') ? 'selected' : ''; ?>>ENSEIGNANT</option>
           
        </select>
    </div>

    <input type="file" id="photo_profil" name="photo_profil" accept="image/*" onchange="previewImage(event)">
    <p>Choisissez une nouvelle photo de profil (laisser vide pour ne pas changer)</p>
    
    <!-- Image prévisualisation -->
    <img id="imagePreview" src="" alt="Aperçu de la photo de profil" style="display:none; width: 100px; height: 100px; border-radius: 50%; margin-top: 10px;">

    <input type="password" id="password" name="password" placeholder="Mot de passe (laisser vide pour ne pas changer)">
    <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
    <div class="checkbox-group">
        <input type="checkbox" id="terms" required>
        <label for="terms">J'accepte les <a href="#">Conditions d'utilisation</a> et la <a href="#">Politique de confidentialité</a></label>
    </div>
    <button type="submit" class="btn-submit">Modifier le compte</button>
    <a href="ADMIN.PHP" class="btn-link">RETOUR AU TABLEAU DE BORD DE L'ADMIN</a>
</form>


<script>
    function previewImage(event) {
        const imagePreview = document.getElementById('imagePreview');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result; // Mettre à jour l'élément <img> avec le contenu de l'image
                imagePreview.style.display = 'block'; // Afficher l'aperçu
            }
            reader.readAsDataURL(file); // Lire le fichier en tant qu'URL de données
        } else {
            imagePreview.src = ""; // Réinitialiser si aucun fichier n'est sélectionné
            imagePreview.style.display = 'none'; // Masquer l'aperçu
        }
    }
</script>


</body>
</html>
