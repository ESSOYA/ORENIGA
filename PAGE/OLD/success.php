<?php
// Message à afficher
$message = "Les informations ont été mises à jour avec succès !";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Succès</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
        }

        .dialog {
            display: none; /* Masquer par défaut */
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-in-out; /* Animation d'apparition */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        // Fonction pour rediriger après un délai
        function redirect() {
            window.location.href = 'INSG.php'; // Changez 'autre_page.php' par votre page cible
        }

        // Afficher la boîte de dialogue avec animation
        window.onload = function() {
            const dialog = document.getElementById('dialog');
            dialog.style.display = 'block'; // Afficher la boîte de dialogue

            // Rediriger après 2 secondes
            setTimeout(redirect, 4000); 
        };
    </script>
</head>
<body>
    <div id="dialog" class="dialog">
        <p><?php echo $message; ?></p>
    </div>
</body>
</html>
