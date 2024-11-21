

<?php
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $conn->real_escape_string($_POST['famille']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $email = $conn->real_escape_string($_POST['email']);
    $date = $conn->real_escape_string($_POST['dob']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $genre = $conn->real_escape_string($_POST['gender']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Vérification si l'email existe déjà dans les tables
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

    if ($result->num_rows > 0) {
        // Rediriger vers une page d'avertissement si l'email existe
        header("Location: account_exists.php?name=" . urlencode($firstname) . " " . urlencode($prenom));
        exit();
    } else {
        // Préparation de la requête SQL en fonction du rôle
        $sql = null;
        if ($role == "ETUDIANT") {
            $sql = "INSERT INTO etudiant (Nom_famille, Prenom, Email, mot_de_passe, DATE_NAISSANCE, Téléphone, Genre) VALUES ('$firstname', '$prenom', '$email', '$password', '$date', '$phone', '$genre')";
        } elseif ($role == "ENSEIGNANT") {
            $sql = "INSERT INTO enseignant (Nom_famille, Prenom, Email, mot_de_passe, DATE_NAISSANCE, Téléphone, Genre) VALUES ('$firstname', '$prenom', '$email', '$password', '$date', '$phone', '$genre')";
        } elseif ($role == "ENTREPRISE") {
            $sql = "INSERT INTO entreprise (Nom_famille, Prenom, Email, mot_de_passe, DATE_NAISSANCE, Téléphone, Genre) VALUES ('$firstname', '$prenom', '$email', '$password', '$date', '$phone', '$genre')";
        } 
        

        if ($sql) {
            if ($conn->query($sql) === TRUE) {
                echo "<script>
                    if (confirm('Confirmez-vous l\'inscription de $firstname $prenom avec le rôle de $role?')) {
                        window.location.href = 'login.php';
                    } else {
                        window.history.back();
                    }
                </script>";
            } else {
                echo "Erreur: " . $conn->error;
            }
        } else {
            echo "Erreur : rôle non reconnu.";
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
    <title>Inscrivez-vous</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 120vh;
        }

        .container {
            width: 100%;
            max-width: 900px;
            min-width: 200px;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 30px;
            
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: transform 0.6s;
        }

        .form-container:hover {
            transform: scale(1.02);
        }

        .logo {
            width: 120px;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
        }

        

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2c5282;
        }

        p {
            color: #4a5568;
            margin-bottom: 20px;
        }

        .input-group {
            
            display: flex;
            gap: 10px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="date"]:focus,
        input[type="password"]:focus,
        select:focus {
            border: 1px solid #8f94fb;
            box-shadow: 0px 0px 6px rgba(142, 142, 255, 0.4);
            outline: none;
        }

        .password-info {
            font-size: 12px;
            color: #718096;
            margin-bottom: 10px;
        }

        .checkbox-group {
            
            display: flex;
            align-items: center;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .checkbox-group label {
            margin-left: 5px;
        }

        .btn-submit {
            background: #4e54c8;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .btn-submit:hover {
            background: #8f94fb;
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        a {
            color: #4e54c8;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="IMAGE/ORENIGA_FULL.png" alt="ORENIGA" class="logo">
            <h2>ESPACE ORENIGA</h2>
            <p>Créez votre compte</p>
            <form id="registrationForm" action="register.php" method="POST" class="sign-up-form">
                <div class="input-group">
                    <input type="text" id="prenom" name="prenom"  placeholder="Prénom" required>
                    <input type="text" id="famille" name="famille" placeholder="Nom de famille" required>
                </div>
                
                <input type="email" id="email" name="email" placeholder="Addresse Email" required>
                <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                <input type="date" id="dob" name="dob" placeholder="Date de Naissance" required>
                <div class="input-group">
                    <select id="gender" name="gender" required>
                        <option value="">GENRE</option>
                        <option value="HOMME">HOMME</option>
                        <option value="FEMME">FEMME</option>
                        <option value="AUTRE">AUTRE</option>
                    </select>
                    <select id="role" name="role" required>
                        <option value="">Role</option>
                        <option value="ETUDIANT">ETUDIANT</option>
                        <option value="ENSEIGNANT">ENSEIGNANT</option>
                        <option value="ENTREPRISE">ENTREPRISE</option>
                    </select>
                </div>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <p class="password-info">Le mot de passe doit contenir au moins 8 caractères, y compris des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</p>
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">J'accepte les <a href="#">Conditions d'utilisation</a> et la <a href="#">Politique de confidentialité</a></label>
                </div> 
                <button type="submit" class="btn-submit">Créer un compte</button>
                <p>Vous avez déjà un compte ? <a href="login.php">Connexion</a></p>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const terms = document.getElementById("terms").checked;

            if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password) || !/[\W_]/.test(password)) {
                alert("Le mot de passe doit contenir au moins 8 caractères, avec une majuscule, une minuscule, un chiffre, et un caractère spécial.");
                return false;
            }

            if (password !== confirmPassword) {
                alert("Les mots de passe ne correspondent pas.");
                return false;
            }

            if (!terms) {
                alert("Vous devez accepter les conditions d'utilisation et la politique de confidentialité.");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
