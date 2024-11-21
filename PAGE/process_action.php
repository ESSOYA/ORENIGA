<?php
session_start();
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$action = $_GET['action'] ?? '';
$etu_id = $_GET['etu_id'] ?? null;
$document_id = $_GET['document_id'] ?? null;
$file_type = $_GET['file_type'] ?? null;

// Liste des types de fichiers autorisés
$allowed_file_types = ['fichier_bac', 'fichier_notes', 'fichier_naissance', 'fichier_identite', 'photos_identite'];

// Function to send notification
function sendNotification($conn, $etu_id, $message) {
    $query = "INSERT INTO notification_personel (etu_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $etu_id, $message);
    $stmt->execute();
    $stmt->close();
}

switch ($action) {
    case 'delete':
        if (!empty($document_id) && in_array($file_type, $allowed_file_types)) {
            $query = "UPDATE documents_inptic SET $file_type = NULL WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $document_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo 'Document supprimé';
            } else {
                echo 'Erreur lors de la suppression du document';
            }
            $stmt->close();
            header("Location: document.php");
            exit;
        } else {
            echo 'Type de fichier non valide ou document_id manquant';
        }
        break;
        
    case 'approve_all':
        if (!empty($etu_id)) {
            $query = "SELECT id FROM documents_inptic WHERE etu_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $etu_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $document_id = $row['id'];
                $queryUpdate = "UPDATE documents SET status = 'approuvé' WHERE document_id = ?";
                $stmtUpdate = $conn->prepare($queryUpdate);
                $stmtUpdate->bind_param('i', $document_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $stmt->close();

            // Send notification to student
            sendNotification($conn, $etu_id, "Votre dossier pour le concours de l'INPTIC a été approuvé. Veuillez maintenant continuer la procédure en contactant notre service d'inscription au [ 066813542 ]. Merci de suivre les étapes indiquées pour finaliser votre candidature.");

            header("Location: document.php");
            exit;
        }
        break;

    case 'reject_all':
        if (!empty($etu_id)) {
            $query = "SELECT id FROM documents_inptic WHERE etu_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $etu_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $document_id = $row['id'];
                $queryUpdate = "UPDATE documents SET status = 'rejeté' WHERE document_id = ?";
                $stmtUpdate = $conn->prepare($queryUpdate);
                $stmtUpdate->bind_param('i', $document_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $stmt->close();

            // Send notification to student
            sendNotification($conn, $etu_id, "Après examen, votre dossier pour le concours de l'INPTIC n’a pas pu être validé. Merci de revoir vos documents et d’effectuer les modifications nécessaires avant une nouvelle soumission.");

            header("Location: document.php");
            exit;
        }
        break;

    default:
        die("l'action n'est pas valide !");
}

$conn->close();
?>
