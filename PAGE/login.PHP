


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
                    // Dans votre script de connexion ou ailleurs
                    $_SESSION['user'] = $user; 
                    $_SESSION['user']['BAC'] = $user['BAC']; // Utilisez $user ici, pas $row
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORENIGA - Connexion</title>
    <style>
        /* Previous CSS remains unchanged */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(45deg, #1a1a1a, #2c3e50);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
            overflow-y: auto;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .background-text {
            position: fixed;
            font-size: 15vw;
            color: rgba(255, 255, 255, 0.03);
            z-index: -1;
            animation: float 10s ease-in-out infinite, glow 2s ease-in-out infinite;
            user-select: none;
            width: 100%;
            text-align: center;
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 5px rgba(255,255,255,0.1); }
            50% { text-shadow: 0 0 20px rgba(255,255,255,0.2); }
        }

        .graduation-cap {
            position: fixed;
            top: 10%;
            right: 15%;
            font-size: 80px;
            color: rgba(255, 255, 255, 0.1);
            animation: floatCap 6s ease-in-out infinite;
            user-select: none;
            transition: transform 0.3s ease;
            z-index: -1;
        }

        .graduation-cap:hover {
            transform: scale(1.2) rotate(10deg);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        @keyframes floatCap {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, -20px) rotate(10deg); }
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 700px;
            margin: 20px auto;
            position: relative;
            animation: containerAppear 1s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        @keyframes containerAppear {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .tab:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .tab.active {
            background: rgba(255, 255, 255, 0.1);
            animation: tabActivate 0.5s ease;
        }

        @keyframes tabActivate {
            0% { transform: scale(0.95); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            transform-origin: left;
            animation: formGroupAppear 0.5s ease-out backwards;
        }

        @keyframes formGroupAppear {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        input:focus, select:focus {
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.5);
            transform: scale(1.02);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }

        select option {
            background: #2c3e50;
            color: white;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: white;
            user-select: none;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            transform: translateY(-50%) scale(1.2);
        }

        .form {
            display: none;
        }

        .form.active {
            display: block;
            animation: formAppear 0.5s ease-out;
        }

        @keyframes formAppear {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* New CSS for password validation */
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: white;
            display: none;
        }

        .password-match-status {
            font-size: 12px;
            margin-top: 5px;
            color: white;
        }

        .valid {
            color: #2ecc71;
        }

        .invalid {
            color: #e74c3c;
        }

        .password-requirements {
            font-size: 12px;
            color: white;
            margin-top: 5px;
            padding-left: 15px;
        }

        .requirement {
            margin: 2px 0;
            transition: color 0.3s ease;
        }

        .requirement.met {
            color: #2ecc71;
        }

        .requirement.unmet {
            color: #e74c3c;
        }

        h3 {
    font-family: 'Poppins', sans-serif; /* Police élégante */
    font-size: 1.8rem; /* Taille réduite */
    color: #ffd700;
    text-align: center; /* Texte centré */
    margin-bottom: 15px; /* Moins d'espacement en bas */
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4); /* Ombre légère */
    background: linear-gradient(45deg, #32cd32, #006400); /* Vert vif et vert foncé intense */

    -webkit-background-clip: text; /* Appliquer le dégradé uniquement au texte */
    -webkit-text-fill-color: transparent; /* Rendre le texte transparent pour afficher le dégradé */
    animation: fadeIn 1.5s ease-in-out; /* Animation d'apparition rapide */
}

.welcome-container {
    text-align: center; /* Centre le contenu */
    margin-top: -10px; /* Ajoute un espace au-dessus de la section */
    margin-bottom: 15px;
}

.welcome-image {
    max-width: 300px; /* Ajuste la taille maximale de l'image */
    height: auto; /* Conserve les proportions de l'image */
    margin-top: -50px; /* Ajoute un espace au-dessus de la section */
    margin-bottom: -55px;
    animation: fadeIn 6.5s ease-in-out; /* Animation d'apparition rapide */

}



/* Animation d'apparition */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px); /* Moins de déplacement vertical */
    }
    to {
        opacity: 1;
        transform: translateY(0); /* Position normale */
    }
}

    </style>
</head>
<body>
    <div class="background-text">ORENIGA</div>
    <div class="graduation-cap">🎓</div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('login', this)">Connexion</div>
            <div class="tab" onclick="switchTab('register', this)">Inscription</div>
        </div>

        <form class="form active" action="login.php" method="POST" id="login" onsubmit="handleSubmit(event)">
        <div class="welcome-container">
    <img src="oreniga.png" alt="Bienvenue" class="welcome-image">
  
</div>

            <div class="form-group">
                <h3>Bonjour ! Connectez-vous.</h3>
                <input type="email" name="EMAIL" placeholder="Email" required autocomplete="email">
            </div>
            <div class="form-group">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password" minlength="6">
                    <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
                </div>
            </div>
            <button type="submit">Se connecter</button>
            <a href="/oreniga/oreniga.html">menu princiapl</a>
        </form>
        

        <form class="form" id="register" action="nouv.php" method="POST" onsubmit="handleSubmit(event)">
            
            <div class="form-group">
            <div class="welcome-container">
    <img src="oreniga.png" alt="Bienvenue" class="welcome-image">
  
</div>
            <h3>Bonjour ! Inscrivez-vous ici .</h3>
                <input type="text" placeholder="Nom" name="famille" required autocomplete="family-name">
            </div>
            <div class="form-group">
                <input type="text" placeholder="Prénom"name="prenom" required autocomplete="given-name">
            </div>
            <div class="form-group">
                <input type="email" placeholder="Email" name="email" required autocomplete="email">
            </div>
            <div class="form-group">
                <input type="tel" placeholder="Numéro de téléphone" name="phone" required >
            </div>
            <div class="form-group">
                <select name="gender" required>
                    <option value="">Genre</option>
                    <option value="homme">Homme</option>
                    <option value="femme">Femme</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <select name="role" required>
                <option value="">Role</option>
                        <option value="ETUDIANT">ETUDIANT</option>
                        <option value="ENSEIGNANT">ENSEIGNANT</option>
                        <option value="ENTREPRISE">ENTREPRISE</option>
                </select>
            </div>
            <div class="form-group">
                <select name="bac" required>
                <option value="">VOTRE BAC</option>
                        <option value="LITTERAIRE(A1)">BAC : A1</option>
                        <option value="ECONOMISTE(B)">BAC : B</option>
                        <option value="SCIENTIFIQUE(C)">BAC : C</option>
                        <option value="SCIENTIFIQUE(D)">BAC : D</option>
                </select>
            </div>
            <div class="form-group">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe" required minlength="6" id="password">
                    <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
                </div>
                <div class="password-requirements">
                    <div class="requirement" id="length">• 8 caractères minimum</div>
                    <div class="requirement" id="uppercase">• Au moins une majuscule</div>
                    <div class="requirement" id="lowercase">• Au moins une minuscule</div>
                    <div class="requirement" id="number">• Au moins un chiffre</div>
                    
                </div>
            </div>
            <div class="form-group">
                <div class="password-container">
                    <input type="password" placeholder="Confirmer le mot de passe" required minlength="6" id="confirm-password">
                    <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
                </div>
                <div class="password-match-status" id="password-match-message"></div>
            </div>
            <button type="submit" id="register-button">S'inscrire</button>
            <a href="/oreniga/oreniga.html">menu princiapl</a>
        </form>
    </div>

    <script>
        function switchTab(formId, tabElement) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form').forEach(form => form.classList.remove('active'));
            tabElement.classList.add('active');
            document.getElementById(formId).classList.add('active');
        }

        function togglePassword(element) {
            const input = element.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                element.textContent = '🔒';
            } else {
                input.type = 'password';
                element.textContent = '👁️';
            }
        }

        function validatePassword(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
            
            };

            // Update requirement visual feedback
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(req);
                if (element) {
                    element.classList.toggle('met', requirements[req]);
                    element.classList.toggle('unmet', !requirements[req]);
                }
            });

            return Object.values(requirements).every(Boolean);
        }

        function updatePasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const matchMessage = document.getElementById('password-match-message');
            const registerButton = document.getElementById('register-button');

            if (confirmPassword) {
                if (password === confirmPassword) {
                    matchMessage.textContent = '✓ Les mots de passe correspondent';
                    matchMessage.className = 'password-match-status valid';
                    registerButton.disabled = false;
                } else {
                    matchMessage.textContent = '✗ Les mots de passe ne correspondent pas';
                    matchMessage.className = 'password-match-status invalid';
                    registerButton.disabled = true;
                }
            } else {
                matchMessage.textContent = '';
                registerButton.disabled = false;
            }
        }

        function handleSubmit(event) {
            
            
            if (event.target.id === 'register') {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                if (!validatePassword(password)) {
                    alert('Le mot de passe ne respecte pas les critères de sécurité requis.');
                    return;
                }

                if (password !== confirmPassword) {
                    alert('Les mots de passe ne correspondent pas!');
                    return;
                }
            }

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            console.log('Form submitted:', data);
        }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    validatePassword(this.value);
                    updatePasswordMatch();
                });
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', updatePasswordMatch);
            }
        });
    </script>
</body>
</html>