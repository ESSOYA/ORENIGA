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

$user_id = $_SESSION['user']['entre_id']; // ID de l'utilisateur entreprise
$user_type = $_SESSION['user']['entre_type']; // Assurez-vous que le type d'utilisateur est 'entreprise'
$type = 'entreprise';
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
        $stmt->bind_param("isss", $user_id, $type, $message, $file_path);
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
    <title>Discussion</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .message {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            display: flex;
            align-items: flex-start; /* Change pour aligner les images avec le texte */
        }
        .message img {
            width: 80px; /* Ajustez la largeur de l'image selon vos besoins */
            height: 80px; /* Ajustez la hauteur de l'image selon vos besoins */
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer; /* Ajoutez un curseur pointer pour indiquer qu'il est cliquable */
        }
        .message-content {
            background: #f1f1f1;
            border-radius: 5px;
            padding: 10px;
            max-width: 75%;
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
            height: 60px;
            margin-bottom: 10px;
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
    </style>
    <script>
        // Affichage de la modale
        function openModal(src) {
            const modal = document.getElementById("myModal");
            const modalImg = document.getElementById("img01");
            modal.style.display = "block";
            modalImg.src = src;

            // Ajouter un gestionnaire d'événements pour fermer la modale en cliquant sur l'image
            modalImg.onclick = function() {
                closeModal();
            };
        }

        // Fermeture de la modale
        function closeModal() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        function previewFile() {
            const file = document.querySelector('input[type=file]').files[0];
            const preview = document.querySelector('.preview');
            const reader = new FileReader();

            reader.onload = function (e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation" style="max-width: 100%; height: auto;">`;
                } else if (file.type.startsWith('video/')) {
                    preview.innerHTML = `<video controls style="max-width: 100%;"><source src="${e.target.result}" type="${file.type}">Votre navigateur ne prend pas en charge la vidéo.</video>`;
                } else {
                    preview.innerHTML = `<p>Document: ${file.name}</p>`;
                }
            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Discussion</h1>

        <!-- Formulaire pour envoyer un message au début -->
        <form action="discussion2.php" method="post" enctype="multipart/form-data">
            <textarea name="message" placeholder="Écrivez un message..." required></textarea>
            <input type="file" name="file" accept="image/*" onchange="previewFile()">
            <div class="preview"></div>
            <button type="submit">Envoyer</button>
        </form>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message">
                <img src="<?php echo htmlspecialchars($row['sender_photo']); ?>" alt="Photo de profil">
                <div class="message-content">
                    <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong> (<?php echo htmlspecialchars($row['sender_type']); ?>) <br>
                    <?php echo htmlspecialchars($row['message']); ?> <br>
                    <?php if ($row['file_path']): ?>
                        <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Image" style="max-width: 100%; height: auto;" onclick="openModal(this.src)">
                    <?php endif; ?>
                    <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                    <form action="discussion2.php" method="post">
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
                <form action="discussion2.php" method="post">
                    <textarea name="reponse" placeholder="Écrivez une réponse..."></textarea>
                    <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
                    <button type="submit">Répondre</button>
                </form>
            </div>
        <?php endwhile; ?>

        <a href="entreprise.php">Retour à mon profil</a> | <a href="logout.php">Se déconnecter</a>
    </div>

    <!-- Modale pour afficher l'image agrandie -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="img01" onclick="closeModal()"> <!-- Ajoutez un gestionnaire d'événements ici -->
    </div>
</body>
</html>

<?php
$conn->close();
?>

