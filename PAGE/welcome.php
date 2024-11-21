<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
$userType = $_SESSION['user_type'];
$photo_profil = $user['Photo_profil'] ?? 'default.jpg'; // Remplacez par une image par défaut si non définie


?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur votre espace <?php echo ucfirst($userType); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .welcome-container {
            max-width: 1200px;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: fadeIn 1s ease-in;
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

        .welcome-logo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 30px;
            border: 4px solid white;
            padding: 10px;
            background: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .welcome-title {
            font-size: 3em;
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .welcome-subtitle {
            font-size: 1.5em;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.9);
        }

        .profile-button {
            padding: 15px 40px;
            font-size: 1.2em;
            background: white;
            color: #1e3c72;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .profile-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            background: #f0f0f0;
        }

        .welcome-footer {
            margin-top: 40px;
            font-size: 1.1em;
            color: rgba(255, 255, 255, 0.8);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            color: #1e3c72;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <a href="#profil"><img src="<?php echo htmlspecialchars($photo_profil); ?>" width="75" alt="Description de l'image" class="welcome-logo" id="image"></a>
      
        <script>
            const img = document.getElementById('image');
            img.addEventListener('error', function() {
                img.src = "404.jpg"; // Définit l'image alternative
                img.alt = "Image alternative"; // Change aussi la description si besoin
            });
        </script>
        
        <h1 class="welcome-title">Bienvenue <?php echo htmlspecialchars($user['Nom_famille']); ?> sur votre espace <?php echo ucfirst($userType); ?></h1>
        <p class="welcome-subtitle">Nous sommes ravis de vous revoir! Votre succès est notre priorité.</p>
        
        <a href="<?php echo htmlspecialchars($userType . '.php'); ?>" class="profile-button">
            Accéder à mon profil
        </a>

        <div class="welcome-footer">
            <p>Dernière connexion: <span id="last-login"></span></p>
        </div>
    </div>

    <div class="notification" id="notification" style="display: none;">
        Connexion réussie!
    </div>

    <script>
        // Afficher la notification de connexion
        window.onload = function() {
            const notification = document.getElementById('notification');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);

            // Mettre à jour la dernière connexion
            const now = new Date();
            const options = { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('last-login').textContent = now.toLocaleDateString('fr-FR', options);
        }
    </script>
</body>
</html>
