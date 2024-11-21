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

$user_id = $_SESSION['user']['ens_id'];
$user_type = $_SESSION['user']['ens_type']; // Assurez-vous que le type d'utilisateur est stocké ici
$type = 'enseignant';
// Gérer l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $file_path = '';

    // Vérification du message texte
    if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = $conn->real_escape_string(trim($_POST['message']));
    }

    // Gestion des fichiers
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_type = $_FILES['file']['type'];

        // Vérification de la taille du fichier (moins de 100 Mo)
        if ($file_size > 100 * 1024 * 1024) {
            echo "<script>alert('Le fichier doit être inférieur à 100 Mo.');</script>";
        } else if (!preg_match("/^image\//", $file_type)) { // Vérification que le fichier est une image
            echo "<script>alert('Seules les images sont autorisées.');</script>";
        } else {
            // Déplacement du fichier vers le dossier de téléchargement
            $upload_dir = 'uploads/';
            $file_path = $upload_dir . basename($file_name);
            move_uploaded_file($file_tmp_path, $file_path);
        }
    }

    // Insertion dans la base de données
    if ($message || $file_path) {
        $sql = "INSERT INTO messages (sender_id, sender_type, message, file_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $user_type, $message, $file_path);
        $stmt->execute();
        $stmt->close();
    }
}

// Gérer le like et le dislike
if (isset($_POST['like']) || isset($_POST['dislike'])) {
    $message_id = intval($_POST['message_id']);
    if (isset($_POST['like'])) {
        $sql = "UPDATE messages SET likes = likes + 1 WHERE id = ?";
    } else {
        $sql = "UPDATE messages SET dislikes = dislikes + 1 WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Gérer la suppression d'un message
if (isset($_POST['delete'])) {
    $message_id = intval($_POST['message_id']);

    // Vérifier si l'utilisateur est le propriétaire du message
    $sql = "SELECT sender_id FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = $result->fetch_assoc();
        if ($message['sender_id'] == $user_id) {
            // L'utilisateur est le propriétaire, on peut supprimer le message
            $sql = "DELETE FROM messages WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $message_id);
            $stmt->execute();
        } else {
            // L'utilisateur n'est pas le propriétaire
            echo "<script>alert('Vous ne pouvez supprimer que vos propres messages.');</script>";
        }
    }
    $stmt->close();
}

// Gérer l'envoi d'une réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $message_id = intval($_POST['message_id']);
    $reponse = $conn->real_escape_string($_POST['reponse']);
    $sql = "INSERT INTO reponses (message_id, reponse, sender_id, sender_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $message_id, $reponse, $user_id, $type);
    $stmt->execute();
    $stmt->close();
}

// Récupérer les messages
$sql = "SELECT m.id, m.message, m.created_at, m.sender_id, m.sender_type, m.likes, m.dislikes, m.file_path,
            CASE 
                WHEN m.sender_type = 'etudiant' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM etudiants WHERE etu_id = m.sender_id)
                WHEN m.sender_type = 'enseignant' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM enseignants WHERE ens_id = m.sender_id)
                WHEN m.sender_type = 'entreprise' THEN (SELECT username FROM entreprises WHERE entre_id = m.sender_id)
                WHEN m.sender_type = 'administrateur' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM administrateur WHERE admin_id = m.sender_id)
            END AS sender_name,
            CASE 
                WHEN m.sender_type = 'etudiant' THEN (SELECT photo_profil FROM etudiants WHERE etu_id = m.sender_id)
                WHEN m.sender_type = 'enseignant' THEN (SELECT photo_profil FROM enseignants WHERE ens_id = m.sender_id)
                WHEN m.sender_type = 'entreprise' THEN (SELECT photo_profil FROM entreprises WHERE entre_id = m.sender_id)
                WHEN m.sender_type = 'administrateur' THEN (SELECT photo_profil FROM administrateur WHERE admin_id = m.sender_id)
            END AS sender_photo,
            m.sender_type
        FROM messages m
        ORDER BY m.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oreniga - Social Media</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: #000000;
            color: #fcefef;
            height: auto;
        }

            @keyframes gradientBackground {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
         } 

        /* Barre latérale */
        .sidebar {

            margin-top: -19px;
            
            width: 300px;
            background-color: #fff;
            padding: 20px;
            height: 1000px;
            border-right: 1px solid #ddd;
            position: fixed;
            
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            animation: float 3s ease-in-out infinite;
            
         
           
            
        }

        .sidebar3 {
            
            width: 420px;
            background-color: #fff;
            padding: 20px;
            height: auto;
            border-right: 1px solid #ddd;
            
            
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
         
           
            
        }

        .sidebar1 {
            right:1px;
            width: 220px;
            background-color: #fff;
            padding: 20px;
            height: 100vh;
            border-right: 1px solid #ddd;
        }

        .sidebar h2 {
            font-size: 24px;
            color: #4e54c8;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #fcefef;
            font-size: 16px;
            padding: 10px 0;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #f0f0f0;
            border-radius: 8px;
        }

        .sidebar a i {
            margin-right: 10px;
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        /* Barre de recherche */
        .header {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header input[type="text"] {
            
            flex: 1;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            color: #fcefef;
            backdrop-filter: blur(15px);
            
            
        }

        input[type="file"] {
        margin: 10px 0;

        text-align: center;
        
            
           
            max-width: auto;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
 
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
           
           
            
            
        
        }

        /* Fil d'actualité */
        .feed {
            margin-bottom: 20px;
        }

        .post-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .post-box textarea {
            width: 100%;
            padding: 5px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            resize: none;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: #4e54c8;
        }

        .actions span {
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .actions span i {
            margin-right: 5px;
        }

        /* Publication */
        .post {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .post-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-header .username {
            font-weight: bold;
            font-size: 14px;
        }

        .post-header .time {
            font-size: 12px;
            color: #888;
        }

        .post-content p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .post-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
        }

        .post-actions {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            color: #4e54c8;
            cursor: pointer;
        }

        /* Liste des amis en ligne */
        .online-friends {
            width: 350px;
            padding: 30px;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);

           
           
        }

        .online-friends h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #fcefef;
        }

        .online-friend {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .online-friend img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .container {
            top: 10px;;
            display: inline-block;
           
            max-width: auto;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);


            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
            border-radius: 15px;
            backdrop-filter: blur(15px);
            
        
            
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
           top: 100px;
           
            
            
        }
        .container1 {
            margin-bottom: 100px;
           
           max-width: auto;
           margin: auto;
           padding: 20px;
           background: white;
           border-radius: 5px;
           box-shadow: 0 0 10px rgba(0,0,0,0.1);
           text-align: center;

           background-color: rgb(255, 255, 255);
           background-color: rgba(255, 255, 255, 0.15);
           
          
          
           
           
       }

       h1{
            
           
            max-width: auto;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
 
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
           
           
            
            
        }
        .message {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            display: flex;
            align-items: flex-start; /* Change pour aligner les images avec le texte */

            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            color: #fcefef;
            
        
        }
    
        .message .pho img {

            
            max-width: 100%;
            min-width: 100%;
           
            
        
        }
        .profil img{
            width: 80px; /* Ajustez la largeur de l'image selon vos besoins */
            height: 80px; /* Ajustez la hauteur de l'image selon vos besoins */
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer; /* Ajoutez un curseur pointer pour indiquer qu'il est cliquable */

            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
            border-radius: 15px;
            backdrop-filter: blur(15px);
            
        
        }
        .message-content {
            background: #f1f1f1;
            border-radius: 5px;
            padding: 10px;
            max-width: 75%;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            color: #fcefef;
            border-radius: 15px;
            
            
        
        }

        .message-content .mess {
            background: #f1f1f1;
            border-radius: 5px;
            padding: 10px;
            max-width: 75%;
            font-size: xx-large;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            color: #6d0a0a;
            border-radius: 15px;
            backdrop-filter: blur(15px);
            
        
        }
        .response {
            margin-left: 60px;
            border-left: 2px solid #ddd;
            padding-left: 10px;
            margin-top: 10px;
            
            
            
        
        }
        form {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            height: auto;
            margin-bottom: 10px;
            border-radius: 15px;
            backdrop-filter: blur(15px);
            font-display: center;
            text-align: center;
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
        .preview {
            margin-top: 10px;
        }
        /* Styles pour la modale */
        .modal {
            display: none; /* Masqué par défaut */
            position: fixed; /* Reste en place */
            z-index: 1000; /* Au-dessus des autres éléments */
            left: 0;
            top: 0;
            width: 100%; /* Largeur de la fenêtre */
            height: 100%; /* Hauteur de la fenêtre */
            overflow: auto; /* Activer le défilement si nécessaire */
            background-color: rgba(0,0,0,0.8); /* Couleur de fond semi-transparente */
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%; /* Largeur de l'image dans la modale */
            max-width: 700px; /* Largeur maximale */
            height: auto; /* Hauteur automatique */
            cursor: pointer; /* Ajoutez un curseur pointer pour indiquer qu'on peut cliquer */
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer; /* Curseur pointer pour le bouton de fermeture */
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .pho img{
            min-width: 100px;
            max-height: 600px;
            min-height: auto;
            max-width: 100px;
            margin: auto;
            padding: 15px,20px;
            border: 1px solid #ccc;
            border-radius: 15px;
            text-align: center;
            background-color: rgb(255, 255, 255);
            
        }

        .floating-logo {
           
    
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(10px); }
        }


    </style>
</head>
<body>

    <!-- Barre latérale -->
    <div class="sidebar3">
    
    </div>
    <div class="sidebar">
        <h2><img src="IMAGE/ORENIGA_FULL.png" width="90" alt="ORENIGA" class="floating-logo"></h2>
        <a href="/orenigo/index.html"><i>🏠</i>Accueil</a>
        <a href="etudiant.php"><i>👤</i>VOIR MON PROFIL</a>
        <a href="#"><i>🙋‍♂️</i>M'INSCRIRE AU CONCOURS INPTIC</a>
        <a href="#"><i>🧏‍♂️</i>M'INSCIRE AU CONCOURS INSG</a>
        <a href="#"><i>🥳</i>INSCRIPTION INPTIC 2024-2025</a>
        <a href="#"><i>🥳</i>INSCRIPTION INSG 2024-2025</a>
        <a href="#"><i>😡</i>SE PLAINDRE CHEZ l'ADMIN</a>
        <a href="user.php"><i>🤓</i>MON SUIVIS</a>
        <a href="inbox.php"><i>📬</i>MON INBOX</a>
        <a href="logout.php"><i>❌</i>Se déconnecter</a>
    </div>
    

    

    <div class="container">
        <h1>Fil d'actualités</h1>

        <!-- Formulaire pour envoyer un message au début -->
        <form action="dashens.php" method="post" enctype="multipart/form-data">
            <textarea name="message" placeholder="Écrivez un message..." required></textarea>
            <input type="file" name="file" accept="image/*" onchange="previewFile()">
            <div class="preview"></div>
            <button type="submit">Envoyer</button>
        </form>

        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="container1" >
            <h1>nouvelle publication</h1>
          <div class="message">
          <?php
                // Construire le lien vers le profil en ajoutant le type et l'ID
                $profile_link = '';
                if ($row['sender_type'] === 'etudiant') {
                    $profile_link = "profil_etudiant.php?type=etudiant&id=" . $row['sender_id'];
                } elseif ($row['sender_type'] === 'enseignant') {
                    $profile_link = "profil_enseignant.php?type=enseignant&id=" . $row['sender_id'];
                } elseif ($row['sender_type'] === 'entreprise') {
                    $profile_link = "profil_entreprise.php?type=entreprise&id=" . $row['sender_id'];
                }elseif ($row['sender_type'] === 'administrateur') {
                    $profile_link = "profil_administrateur.php?type=administrateur&id=" . $row['sender_id'];
                }
            
 
?>
            <a href="<?php echo $profile_link; ?>">
                <div class="profil">
                    <img src="<?php echo htmlspecialchars($row['sender_photo']); ?>" alt="Photo de profil">
                </div>
            </a>
                
                <div class="message-content">
                    <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong> (<?php echo htmlspecialchars($row['sender_type']); ?>) <br> <br>
                    <div class="mess"><?php echo htmlspecialchars($row['message']); ?> </div><br> <br>
                    <?php if ($row['file_path']): ?>
                        <DIV class="pho" ><img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Image" style="max-width: 100%; height: auto;" onclick="openModal(this.src)"></DIV>
                    <?php endif; ?>
                    <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                    <form action="dashens.php" method="post">
                        <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="like">Like (<?php echo $row['likes']; ?>)</button>
                        <button type="submit" name="dislike">Dislike (<?php echo $row['dislikes']; ?>)</button>
                        <?php if ($row['sender_id'] == $user_id): ?>
                            <button type="submit" name="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">Supprimer</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Affichage des réponses -->
            <div class="response">
                <h4>Réponses :</h4>
                <?php
                // Récupérer les réponses pour ce message
                $message_id = $row['id'];
                $sql_responses = "SELECT r.reponse, r.created_at, r.sender_id, r.sender_type,
                                    CASE 
                                        WHEN r.sender_type = 'etudiant' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM etudiants WHERE etu_id = r.sender_id)
                                        WHEN r.sender_type = 'enseignant' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM enseignants WHERE ens_id = r.sender_id)
                                        WHEN r.sender_type = 'entreprise' THEN (SELECT username FROM entreprises WHERE entre_id = r.sender_id)
                                        WHEN r.sender_type = 'administrateur' THEN (SELECT CONCAT(Nom_famille, ' ', prénom) FROM administrateur WHERE admin_id = r.sender_id)
                                    END AS responder_name
                                    FROM reponses r
                                    WHERE r.message_id = ?
                                    ORDER BY r.created_at ASC";

                $stmt_responses = $conn->prepare($sql_responses);
                $stmt_responses->bind_param("i", $message_id);
                $stmt_responses->execute();
                $result_responses = $stmt_responses->get_result();

                while ($response = $result_responses->fetch_assoc()): ?>
                    <div>
                        <strong><?php echo htmlspecialchars($response['responder_name']); ?></strong> : <?php echo htmlspecialchars($response['reponse']); ?> <br>
                        <small><?php echo htmlspecialchars($response['created_at']); ?></small>
                    </div>
                <?php endwhile; ?>
                <form action="dashens.php" method="post">
                    <textarea name="reponse" placeholder="Écrivez une réponse..."></textarea>
                    <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
                    <button type="submit">Répondre</button>
                </form>
            </div> 
                    <br> <br><br>
                    <h1>fin de la publication</h1>
          </div>
        <?php endwhile; ?>

        <a href="etudiant.php">Retour à mon profil</a> | <a href="logout.php">Se déconnecter</a>
    </div>

    <!-- Modale pour afficher l'image agrandie -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="img01" onclick="closeModal()"> <!-- Ajoutez un gestionnaire d'événements ici -->
    </div>

    <div class="online-friends">
        <h2>MES CONTACTS</h2>
       
    </div>
    <div class="online-friend">

    </div>

    

</body>
</html>


<?php
$conn->close();
?>

