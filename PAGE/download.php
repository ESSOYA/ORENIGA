<?php
if (isset($_GET['file']) && isset($_GET['name'])) {
    $file = urldecode($_GET['file']);
    $filename = urldecode($_GET['name']);

    // Vérifie si le fichier existe
    if (file_exists($file)) {
        // Obtenir l'extension du fichier
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        // Définit les en-têtes pour le téléchargement
        header('Content-Description: File Transfer');
        
        // Si c'est une image
        if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
            header('Content-Type: ' . mime_content_type($file)); // Type MIME de l'image
            header('Content-Disposition: attachment; filename="' . basename($filename) . '.' . $fileExtension . '"');
        }
        // Si c'est un fichier PDF ou autre
        else {
            header('Content-Type: application/octet-stream'); // Par défaut, pour les autres fichiers
            header('Content-Disposition: attachment; filename="' . basename($filename) . '.pdf"'); // Vous pouvez modifier l'extension si nécessaire
        }

        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "Le fichier demandé n'existe pas.";
    }
} else {
    echo "Paramètres de téléchargement manquants.";
}
?>
