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

// Vérifiez si les données ont été envoyées via POST
if (isset($_POST['etu_id']) && isset($_POST['raison_rejet'])) {
    // Récupérer l'ID de l'étudiant et la raison du rejet
    $etu_id = $_POST['etu_id'];
    $raison_rejet = $_POST['raison_rejet'];

    // Validation des données (par exemple, pour éviter les injections SQL)
    $etu_id = htmlspecialchars($etu_id);
    $raison_rejet = htmlspecialchars($raison_rejet);

    try {
        // Commencer une transaction pour garantir que les deux opérations se produisent ou aucune
        $pdo->beginTransaction();

        // Mettre à jour le statut du document dans la table `documents`
        $sql_documents = "UPDATE documents SET status = 'rejeté' WHERE document_id = :etu_id";
        $stmt_documents = $pdo->prepare($sql_documents);
        $stmt_documents->execute(['etu_id' => $etu_id]);

        // Préparer le message de notification
        $message = "Bonjour,\n\nVotre dossier a été rejeté pour la raison suivante :\n\n" . $raison_rejet . "\n\nCordialement,\nL'administration";

        // Insérer la notification dans la table `notification_personel`
        $sql_notification = "INSERT INTO notification_personel (etu_id, message, status) VALUES (:etu_id, :message, 'non_lu')";
        $stmt_notification = $pdo->prepare($sql_notification);
        $stmt_notification->execute(['etu_id' => $etu_id, 'message' => $message]);

        // Si tout a réussi, commit les changements
        $pdo->commit();

        // Envoi de l'email à l'étudiant
        // Ici vous pouvez utiliser la fonction mail() ou PHPMailer pour envoyer l'email
        $sql_email = "SELECT email FROM etudiants WHERE etu_id = :etu_id";
        $stmt_email = $pdo->prepare($sql_email);
        $stmt_email->execute(['etu_id' => $etu_id]);
        $etudiant = $stmt_email->fetch();

        if ($etudiant) {
            $etudiant_email = $etudiant['email'];
            $subject = "Notification de rejet de dossier";
            $headers = "From: admin@votresite.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            mail($etudiant_email, $subject, $message, $headers);
        }

        // Réponse pour AJAX en cas de succès
        echo json_encode(["status" => "success", "message" => "Le dossier a été rejeté et la notification a été envoyée."]);

    } catch (PDOException $e) {
        // En cas d'erreur de la base de données
        $pdo->rollBack(); // Annuler les changements si une erreur se produit
        echo json_encode(["status" => "error", "message" => "Erreur lors du rejet du dossier : " . $e->getMessage()]);
    }
} else {
    // Si les données nécessaires ne sont pas présentes
    echo json_encode(["status" => "error", "message" => "Données manquantes (etu_id ou raison_rejet)."]);
}
?>
