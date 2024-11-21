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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Récupérer le bac de l'étudiant depuis la base de données
$user_id = $_SESSION['user']['etu_id'];
$sql = "SELECT BAC FROM etudiant WHERE etu_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Si l'étudiant est trouvé, récupérer le bac
$user_bac = $row['BAC'];  // Le bac de l'étudiant
$stmt->close();

// Définir les filières disponibles selon le bac
$choices = [
    'LITTERAIRE(A1)' => ['MTIC'],  // Bac Littéraire ou Economiste
    'ECONOMISTE(B)' => ['MTIC'],  // Bac Economiste
    'SCIENTIFIQUE(C)' => ['RT', 'GI', 'MTIC'],  // Bac Scientifique (C)
    'SCIENTIFIQUE(D)' => ['RT', 'GI', 'MTIC'],  // Bac Scientifique (D)
];

// Vérifier si le bac est valide et récupérer les filières disponibles
$available_filieres = isset($choices[$user_bac]) ? $choices[$user_bac] : [];

if (empty($available_filieres)) {
    echo "<p>Aucune filière disponible pour ce bac ($user_bac).</p>";
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filiere = trim($conn->real_escape_string($_POST['filiere']));

    // Mettre à jour la filière dans la base de données
    $sql = "UPDATE etudiant SET filiere = ? WHERE etu_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $filiere, $user_id);

    if ($stmt->execute()) {
        header("Location: inscription_concours.php");
        exit();
    } else {
        echo "Erreur : Impossible de mettre à jour la filière.";
    }

    $stmt->close();
}

$conn->close();
?>




<!-- <!DOCTYPE html>
<html>
<head>
    <title>Ajouter Filière</title>
</head>
<body>
    <h1>Ajouter votre filière</h1>
    <form method="POST" action="ajouter_filiere.php">
        <label for="filiere">Sélectionnez votre filière :</label>
        <select name="filiere" id="filiere" required>
            <option value="">-- Choisir une filière --</option>
            <option value="RT">RT</option>
            <option value="GI">GI</option>
            <option value="MTIC">MTIC</option>
        </select>
        <button type="submit">Valider</button>
    </form>
</body>
</html> -->


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choix de Filière</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .options {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .option-card {
            background: rgba(255, 255, 255, 0.2);
            padding: 2rem;
            border-radius: 15px;
            width: 200px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }

        .option-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.3);
        }

        .option-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .option-description {
            font-size: 0.9rem;
            line-height: 1.4;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message {
            margin-bottom: 2rem;
            font-size: 1.2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Choix de Filière pour le Concours</h1>
    <div>
        <p>Veuillez choisir une filière parmi les options suivantes :</p>
    </div>

    <form action="ajouter_filiere.php" method="POST">
        <div class="options">
            <?php
            // Afficher les options de filière basées sur le bac de l'utilisateur
            foreach ($available_filieres as $filiere) {
                echo "<label class='option-card'>
                        <input type='radio' name='filiere' value='$filiere' style='display: none;' required>
                        <div class='option-title'>$filiere</div>
                      </label>";
            }
            ?>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="submit-button">Valider votre choix</button>
        </div>
    </form>
        </div>


<script>
    // Animation des cartes au chargement
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.option-card');
        cards.forEach((card, index) => {
            card.style.animation = `fadeIn 0.5s ease-out ${index * 0.2}s forwards`;
            card.style.opacity = '0';

            // Ajouter un événement pour mettre en évidence la sélection
            card.addEventListener('click', function () {
                cards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                card.querySelector('input').checked = true;
            });
        });
    });
</script>

<style>
    .option-card {
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 20px;
        margin: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .option-card:hover {
    background-color: #0f0f0f;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.option-card.selected {
    border-color: #007BFF;
    background-color: #080808;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

    .submit-button {
        background-color: #007BFF;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .submit-button:hover {
        background-color: #0056b3;
    }
</style>

</body>
</html>
