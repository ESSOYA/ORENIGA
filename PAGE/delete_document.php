<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les paramètres depuis l'URL
$action = $_GET['action'] ?? '';
$document_id = $_GET['document_id'] ?? null;
$file_type = $_GET['file_type'] ?? null;

// Liste des colonnes de fichiers autorisées
$allowed_file_types = ['fichier_bac', 'fichier_notes', 'fichier_naissance', 'fichier_identite', 'photos_identite'];

// Vérifier que l'action est bien "delete" et que le type de fichier est valide
if ($action === 'delete' && in_array($file_type, $allowed_file_types) && !empty($document_id)) {
    // Requête préparée pour mettre à NULL le champ spécifique du fichier
    $query = "UPDATE documents_inptic SET $file_type = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $document_id);
    $stmt->execute();

    // Vérifier le résultat
    if ($stmt->affected_rows > 0) {
        echo 'Document supprimé avec succès.';
    } else {
        echo 'Erreur lors de la suppression du document ou document déjà supprimé.';
    }

    // Redirection après traitement
    header("Location: document.php");
    exit;
} else {
    echo 'Action non valide ou paramètres manquants.';
}

// Fermer la connexion
$stmt->close();
$conn->close();
?>
