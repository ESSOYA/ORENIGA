<<?php
session_start();
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Récupérer l'ID de l'étudiant depuis la session
$etudiant_id = $_SESSION['etu_id'] ?? null;
$documents_exist = false;
$documents = [];

// Vérifier si des documents existent déjà pour cet étudiant
if ($etudiant_id) {
    $stmt = $conn->prepare("SELECT * FROM documents_inptic WHERE etu_id = ?");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $documents = $result->fetch_assoc();
        $documents_exist = true;
    }
    $stmt->close();
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = "INPTIC/";
    $telephone = $_POST['telephone'];

    // Définir les noms de fichiers, en utilisant les fichiers existants si aucun nouveau fichier n'est téléchargé
    $fichier_bac = !empty($_FILES["fichier_bac"]["name"]) ? $uploadDir . basename($_FILES["fichier_bac"]["name"]) : ($documents['fichier_bac'] ?? '');
    $fichier_notes = !empty($_FILES["fichier_notes"]["name"]) ? $uploadDir . basename($_FILES["fichier_notes"]["name"]) : ($documents['fichier_notes'] ?? '');
    $fichier_naissance = !empty($_FILES["fichier_naissance"]["name"]) ? $uploadDir . basename($_FILES["fichier_naissance"]["name"]) : ($documents['fichier_naissance'] ?? '');
    $fichier_identite = !empty($_FILES["fichier_identite"]["name"]) ? $uploadDir . basename($_FILES["fichier_identite"]["name"]) : ($documents['fichier_identite'] ?? '');
    $photos_identite = !empty($_FILES["photos_identite"]["name"]) ? $uploadDir . basename($_FILES["photos_identite"]["name"]) : ($documents['photos_identite'] ?? '');

    // Déplacer les nouveaux fichiers vers le dossier de destination
    if (!empty($_FILES["fichier_bac"]["tmp_name"])) move_uploaded_file($_FILES["fichier_bac"]["tmp_name"], $fichier_bac);
    if (!empty($_FILES["fichier_notes"]["tmp_name"])) move_uploaded_file($_FILES["fichier_notes"]["tmp_name"], $fichier_notes);
    if (!empty($_FILES["fichier_naissance"]["tmp_name"])) move_uploaded_file($_FILES["fichier_naissance"]["tmp_name"], $fichier_naissance);
    if (!empty($_FILES["fichier_identite"]["tmp_name"])) move_uploaded_file($_FILES["fichier_identite"]["tmp_name"], $fichier_identite);
    if (!empty($_FILES["photos_identite"]["tmp_name"])) move_uploaded_file($_FILES["photos_identite"]["tmp_name"], $photos_identite);

    // Mise à jour ou insertion selon l'existence des documents
    if ($documents_exist) {
        $stmt = $conn->prepare("UPDATE documents_inptic SET fichier_bac=?, fichier_notes=?, fichier_naissance=?, fichier_identite=?, photos_identite=?, telephone=? WHERE etu_id=?");
        $stmt->bind_param("ssssssi", $fichier_bac, $fichier_notes, $fichier_naissance, $fichier_identite, $photos_identite, $telephone, $etudiant_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO documents_inptic (fichier_bac, fichier_notes, fichier_naissance, fichier_identite, photos_identite, telephone, etu_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $fichier_bac, $fichier_notes, $fichier_naissance, $fichier_identite, $photos_identite, $telephone, $etudiant_id);
    }

    if ($stmt->execute()) {
        echo "Les informations ont été mises à jour avec succès !";

        // Insertion dans la table `documents`
        $doc_id_inptic = $conn->insert_id; // ID du document inséré ou mis à jour
        $current_date = date('Y-m-d');
        $default_status = 'pending';

        $stmt_documents = $conn->prepare("INSERT INTO documents (document_id, date, status) VALUES (?, ?, ?)");
        $stmt_documents->bind_param("iss", $doc_id_inptic, $current_date, $default_status);
        $stmt_documents->execute();
        $stmt_documents->close();

        // Redirection vers une autre page (par exemple, "success.php")
        header("Location: succes.php");
        exit; // Assurez-vous d'utiliser exit après header pour arrêter l'exécution du script
    } else {
        echo "Erreur : " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <style>
        /* Styles CSS */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 130vh; background-color: #f0f2f5; color: #333; }
        .dashboard { background-color: #ffffff; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); text-align: center; }
        .dashboard h1 { font-size: 1.8rem; color: #0D8A78; margin-bottom: 1rem; }
        .dashboard p { font-size: 0.95rem; color: #666; margin-bottom: 2rem; }
        form label { display: block; font-size: 0.9rem; color: #555; margin-bottom: 0.5rem; text-align: left; }
        form input[type="text"] { width: 100%; padding: 0.8rem; margin-bottom: 1.5rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; transition: border-color 0.3s ease; }
        form input[type="text"]:focus { outline: none; border-color: #0D8A78; box-shadow: 0 0 5px rgba(13, 138, 120, 0.3); }
        .file-upload { display: grid; gap: 1.2rem; }
        .file-slot { border: 2px dashed #0D8A78; padding: 1.2rem; border-radius: 8px; position: relative; cursor: pointer; text-align: center; transition: background-color 0.3s ease, border-color 0.3s ease; }
        .file-slot:hover { background-color: #f4faf8; border-color: #0a705f; }
        .file-slot input[type="file"] { opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer; }
        .file-slot span { color: #0D8A78; font-size: 0.95rem; font-weight: 600; }
        .selected-file { color: #0a705f; font-weight: bold; display: block; margin-top: 0.5rem; font-size: 0.85rem; }
        button { width: 100%; padding: 1rem; background-color: #0D8A78; color: #ffffff; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; margin-top: 1.5rem; transition: background-color 0.3s ease, transform 0.2s ease; }
        button:hover { background-color: #0a705f; transform: translateY(-2px); }
        button:active { transform: translateY(0); }
        .floating-button {
        position: fixed;
       
        bottom:20px;
        background-color: #007bff; /* Couleur de fond */
        color: #fff; /* Couleur du texte */
        padding: 15px 20px;
        border-radius: 50%;
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s;
    }

    /* Effet de survol */
    .floating-button:hover {
        background-color: #0056b3;
    }
    </style>
</head>
<body>

<div class="dashboard">
    <h1>Tableau de Bord</h1>

    <?php if ($documents_exist): ?>
        <p>Vous avez déjà téléversé vos documents. Vous pouvez les modifier ci-dessous.</p>
        <form id="upload-form" method="post" enctype="multipart/form-data">
            <label for="telephone">Numéro de téléphone</label>
            <input type="text" id="telephone" name="telephone" placeholder="Format: 06 12 34 56 78" value="<?php echo htmlspecialchars($documents['telephone'] ?? ''); ?>" required>

            <div class="file-upload">
    <?php
    $files = [
        'fichier_bac' => 'Fichier Bac',
        'fichier_notes' => 'Relevé de Notes',
        'fichier_naissance' => 'Acte de Naissance',
        'fichier_identite' => 'Carte d\'Identité',
        'photos_identite' => 'Photo d\'Identité'
    ];

    foreach ($files as $key => $label) {
        $currentFile = $documents[$key] ?? '';
        echo '<div class="file-slot">';
        echo "<label for=\"$key\">$label</label>";
        echo "<input type=\"file\" id=\"$key\" name=\"$key\" accept=\".pdf,.jpg\">";

        // Si un fichier actuel existe
        if ($currentFile) {
            echo "<div class=\"selected-file\">";
            echo "<span>Fichier actuel :</span> ";
            
            // Vérifiez si le fichier est une image (par extension)
            $fileExtension = pathinfo($currentFile, PATHINFO_EXTENSION);
            if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png'])) {
                // Afficher la miniature si c'est une image
                echo "<a href=\"$currentFile\" target=\"_blank\"><img src=\"$currentFile\" alt=\"$label\" style=\"width: 100px; height: auto; border-radius: 5px;\"></a>";
            } else {
                // Sinon, afficher un lien
                echo "<a href=\"$currentFile\" target=\"_blank\">" . basename($currentFile) . "</a>";
            }
            echo "</div>";
        }
        echo '</div>';
    }
    ?>
</div>


            <button type="submit">Mettre à jour les documents</button>
        </form>
    <?php else: ?>
        <p>Veuillez téléverser vos documents pour compléter votre profil.</p>
        <form id="upload-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <label for="phone">Numéro de téléphone</label>
        <input type="text" id="phone" name="telephone" placeholder="Format: 06 12 34 56 78">

        <div class="file-upload">
            <div class="file-slot">
                <label for="file1">Fichier 1</label>
                <input type="file" id="file1" name="fichier_bac" accept=".pdf">
                <span>Cliquez pour téléverser un fichier</span>
                <span class="selected-file" id="selected-file1"></span>
            </div>

            <div class="file-slot">
                <label for="file2">Fichier 2</label>
                <input type="file" id="file2" name="fichier_notes" accept=".pdf">
                <span>Cliquez pour téléverser un fichier</span>
                <span class="selected-file" id="selected-file2"></span>
            </div>

            <div class="file-slot">
                <label for="file3">Fichier 3</label>
                <input type="file" id="file3" name="fichier_naissance" accept=".pdf">
                <span>Cliquez pour téléverser un fichier</span>
                <span class="selected-file" id="selected-file3"></span>
            </div>

            <div class="file-slot">
                <label for="file4">Fichier 4</label>
                <input type="file" id="file4" name="fichier_identite" accept=".pdf">
                <span>Cliquez pour téléverser un fichier</span>
                <span class="selected-file" id="selected-file4"></span>
            </div>

            <div class="file-slot">
                <label for="file5">Fichier 5</label>
                <input type="file" id="file5" name="photos_identite" accept=".jpg">
                <span>Cliquez pour téléverser un fichier</span>
                <span class="selected-file" id="selected-file5"></span>
            </div>
        </div>

        <button type="submit">Envoyer</button>
    </form>
    <?php endif; ?>
</div>
<a href="DASH.php" class="floating-button">Menu</a>
<script>
    document.querySelectorAll('.file-slot input[type="file"]').forEach(input => {
        input.addEventListener('change', (event) => {
            const span = event.target.nextElementSibling;
            span.textContent = "Fichier sélectionné : " + input.files[0].name;
        });
    });
</script>

</body>
</html>
