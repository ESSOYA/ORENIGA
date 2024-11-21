<?php
session_start();

$host = 'localhost';
$dbname = 'ORENIGA';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

$errorMessage = ""; // Variable pour stocker les messages d'erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $EMAIL = trim($conn->real_escape_string($_POST['EMAIL']));
    $inputPassword = $_POST['password'];

    // Vérifier que les champs ne sont pas vides
    if (empty($EMAIL) || empty($inputPassword)) {
        $errorMessage = "Veuillez entrer un nom d'utilisateur et un mot de passe.";
    } else {
        // Définir la variable de redirection
        $redirectUrl = null;

        // Vérifier dans les tables des utilisateurs
        $tables = [
            ['table' => 'etudiant', 'id_field' => 'etu_id', 'redirect' => 'welcome.php'],
            ['table' => 'enseignant', 'id_field' => 'ens_id', 'redirect' => 'welcome.php'],
            ['table' => 'entreprise', 'id_field' => 'entre_id', 'redirect' => 'welcome.php'],
            ['table' => 'administrateur', 'id_field' => 'admin_id', 'redirect' => 'welcome.php'],
            ['table' => 'personel', 'id_field' => 'pers_id', 'redirect' => 'welcome.php']
        ];

        foreach ($tables as $userTable) {
            $sql = "SELECT * FROM {$userTable['table']} WHERE Email='$EMAIL'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($inputPassword, $user['Mot_de_passe'])) {
                    // Stocker toutes les informations de l'utilisateur
                    $_SESSION['user'] = $user; 
                    $_SESSION['user_type'] = $userTable['table']; // Stocker le type d'utilisateur
                    $_SESSION[$userTable['id_field']] = $user[$userTable['id_field']]; // Stocker user_id dans la session
                    $redirectUrl = $userTable['redirect'];
                    break; // Sortir de la boucle si l'utilisateur est trouvé
                }
            }
        }

        // Rediriger l'utilisateur si trouvé ou afficher une erreur
        if ($redirectUrl) {
            header("Location: $redirectUrl");
            exit();
        } else {
            $errorMessage = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}

$conn->close();
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONNECTEZ-VOUS</title>
    <style>
        /* Reset and base styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Background gradient */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #3b002f, #0d014e);
        }

        /* Login card styling */
        .login-card {
            background: #fff;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
        }

        /* Logo and title */
        .login-card h2 {
            font-size: 1.75rem;
            color: #333;
            margin-bottom: 0.5rem;
            animation: scale 5s ease-in-out infinite
        }

        .login-card p {
            
            margin-bottom: 1.5rem;
        }

        /* Input fields */
        .login-card input[type="text"],
        .login-card input[type="email"],
        .login-card input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .login-card input[type="text"]:focus,
        .login-card input[type="password"]:focus {
            border-color: #4CA1AF;
            box-shadow: 0 0 5px rgba(76, 161, 175, 0.5);
            outline: none;
        }

        /* Remember me and button */
        .login-card .remember-me {
            display: flex;
            align-items: center;
            justify-content: start;
            margin-bottom: 1rem;
            color: #555;
            font-size: 0.9rem;
        }

        .login-card .remember-me input {
            margin-right: 0.5rem;
        }

        .login-card button {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            color: #fff;
            background-color: #4CA1AF;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .login-card button:hover {
            background-color: #1e3c72;
            transform: scale(1.05);
        }

        /* Links */
        .login-card .links {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .login-card a {
            color: #4CA1AF;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .login-card a:hover {
            color: #1e3c72;
        }

        /* Error message */
        .error-message {
            color: red;
            margin: 10px 0;
        }

        .floating-logo {
            width: 220px;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(10); }
            50% { transform: translateY(-20px); }
        }
        
        .effect{
           

        }
        @keyframes blur {
            0%, 100%{
                filter: blur(0);}
                50%{filter: blur(5px);}
        }

        @keyframes color {
            0%, 100%{
                opacity: 1;}
                50%{opacity: 0.1;}
        }

        @keyframes scale {
            0%, 100%{
                transform: sacle(1);}
                50%{transform: scale(1.4);}
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="ORENIGA.png" alt="ORENIGA" class="floating-logo">
        <p>Bon retour! connectez-vous à votre compte.</p>

        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" class="sign-in-form" onsubmit="submitForm(event)">
            <input type="email" placeholder="Votre Email" required id="EMAIL" name="EMAIL">
            <input type="password" placeholder="Mot de passe" required id="password" name="password">
            <div class="remember-me">
                <input type="checkbox" id="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit">CONNECTION</button>
        </form>
        <div class="links">
            <a href="/oreniga/oreniga2.html">MOT DE PASSE OUBLIER ?</a>
            <a href="register.php">S'INSCRIRE</a>
        </div>
    </div>

    <script>
        // Adding interactive animation when clicking the login button
        function submitForm(event) {
            event.preventDefault(); // Prevent form submission
            const button = event.target.querySelector('button');
            button.innerHTML = 'CONNECTION EN COURS ...';
            button.style.transform = 'scale(0.95)';

            // Allow the PHP code to process the form
            setTimeout(() => {
                
                event.target.submit(); // Submit the form after animation
            }, 2000); // Simulate a delay
            alert.document("CONNECTION REUSSIE!")
        }

        // Focus animation
        const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.borderColor = '#4CA1AF';
            });
            input.addEventListener('blur', () => {
                input.style.borderColor = '#ddd';
            });
        });
    </script>
</body>
</html>
