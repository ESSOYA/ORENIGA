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

// Vérifier les paramètres de l'URL
$action = $_GET['action'] ?? '';
$document_id = $_GET['document_id'] ?? null;



if ($action === 'delete_all' && !empty($document_id)) {
    // Supprimer d'abord la ligne dans la table `documents_inptic`
    $queryInptic = "DELETE FROM documents_inptic WHERE id = ?";
    $stmtInptic = $conn->prepare($queryInptic);
    $stmtInptic->bind_param('i', $document_id);
    $stmtInptic->execute();

    if ($stmtInptic->affected_rows > 0) {
        // Ensuite, supprimer la ligne correspondante dans la table `documents`
        $queryDocuments = "DELETE FROM documents WHERE document_id = ?";
        $stmtDocuments = $conn->prepare($queryDocuments);
        $stmtDocuments->bind_param('i', $document_id);
        $stmtDocuments->execute();

        if ($stmtDocuments->affected_rows > 0) {
            echo 'Ligne supprimée de documents_inptic et documents.';
        } else {
            echo 'Ligne supprimée de documents_inptic, mais aucune ligne correspondante trouvée dans documents.';
        }
    } else {
        echo 'Aucune ligne trouvée avec cet ID dans documents_inptic.';
    }

    // Fermer les statements
    $stmtInptic->close();
    $stmtDocuments->close();
    
    // Redirection vers la page des documents après suppression
    header("Location: document.php");
    exit;
} else {
    echo 'Action non valide ou document_id manquant.';
}

// Fermer la connexion
$conn->close();
?>
