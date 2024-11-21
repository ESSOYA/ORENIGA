<?php
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

$name = isset($_GET['name']) ? $_GET['name'] : 'Utilisateur';
$email = ''; // Vous pouvez récupérer l'email si nécessaire pour afficher la photo de profil

// Vous pouvez effectuer une requête pour récupérer l'email et la photo de profil de l'utilisateur
// Exemple :
$checkEmailQuery = "
SELECT Email FROM etudiant WHERE Email = '$email'
UNION ALL
SELECT Email FROM enseignant WHERE Email = '$email'
UNION ALL
SELECT Email FROM entreprise WHERE Email = '$email'
UNION ALL
SELECT Email FROM personel WHERE Email = '$email'
UNION ALL
SELECT Email FROM administrateur WHERE Email = '$email'
";

$result = $conn->query($checkEmailQuery);
$photo = '';
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $photo = $row['Photo_profil'];
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - Compte Existant</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .error-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 450px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #feca57);
        }

        .error-icon {
            color: #e74c3c;
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        .profile-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 15px;
            border: 1px solid #e9ecef;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .user-name {
            color: #2d3436;
            font-size: 1.4rem;
            font-weight: bold;
            margin: 10px 0;
        }

        h1 {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .login-button {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.2rem;
            }

            .profile-image {
                width: 90px;
                height: 90px;
            }

            .user-name {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Ce compte existe déjà</h1>
        
        <div class="profile-section">
            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Photo de profil" class="profile-image" id="Image">
            <div class="user-name" id="userName">********</div>
        </div>

        <p>Un compte utilisant cette adresse email est déjà enregistré dans notre système. Il semble que ce compte appartient à <span id="userNameSpan">Jean Dupont</span>. Veuillez vous connecter avec vos identifiants existants.</p>
        <a href="#" class="login-button" onclick="redirectToLogin()">Se connecter</a>
    </div>
    

    <script>
        const img = document.getElementById('Image'); // Corrigé ici pour correspondre à l'ID dans le HTML

        img.addEventListener('error', function() {
            img.src = "404.jpg"; // Définit l'image alternative
            img.alt = "Image alternative"; // Change aussi la description si besoin
        });

        // Simulating user data - In a real application, this would come from your backend
        const userData = {
            name: "********",
            Image: "<?php echo htmlspecialchars($photo); ?>"
        };

        // Update the DOM with user data
        document.getElementById('userName').textContent = userData.name;
        document.getElementById('userNameSpan').textContent = userData.name;
        document.getElementById('Image').src = userData.Image;

        function redirectToLogin() {
            // Vous pouvez changer cette URL pour votre vraie page de connexion
            alert("Redirection vers la page de connexion...");
            window.location.href = "login.php"; // Décommentez pour activer la redirection
        }
    </script>
</body>
</html>


<?php
$conn->close();
?>
