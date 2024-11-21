<?php
// Message à afficher
$message = "Les informations ont été mises à jour avec succès !";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'enregistrement - INPTIC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .confirmation-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .confirmation-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #4CAF50, #45a049);
        }

        .title-container {
            position: relative;
            height: 150px;
            margin-bottom: 30px;
        }

        .main-title {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin: 0;
            position: relative;
            animation: titleGlow 2s infinite;
        }

        .grad-cap {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 40px;
            animation: jumpCap 2s infinite;
        }

        @keyframes jumpCap {
            0% { transform: translateX(-50%) translateY(0) rotate(0deg); }
            50% { transform: translateX(-50%) translateY(-30px) rotate(10deg); }
            100% { transform: translateX(-50%) translateY(0) rotate(0deg); }
        }

        @keyframes titleGlow {
            0% { text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
            50% { text-shadow: 0 0 20px rgba(76, 175, 80, 0.5); }
            100% { text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .checkmark {
            color: white;
            font-size: 50px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
        }

        p {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 16px;
        }

        .loading {
            width: 80%;
            height: 6px;
            background-color: #f0f0f0;
            border-radius: 3px;
            margin: 25px auto;
            overflow: hidden;
            position: relative;
        }

        .loading-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            animation: loading 3s ease-in-out forwards;
            border-radius: 3px;
        }

        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            color: #666;
        }

        .info-item i {
            margin-right: 10px;
            color: #4CAF50;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @keyframes loading {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        @media (max-width: 480px) {
            .confirmation-container {
                padding: 1.5rem;
            }

            .main-title {
                font-size: 36px;
            }

            h1 {
                font-size: 24px;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="title-container">
            <i class="fas fa-graduation-cap grad-cap"></i>
            <h1 class="main-title">ORENIGA</h1>
        </div>
        <div class="success-icon">
            <span class="checkmark">✓</span>
        </div>
        <h1>Documents Enregistrés avec Succès!</h1>
        <p>Vos documents pour le concours de l'INPTIC ont été correctement enregistrés dans notre système.</p>
        
        <div class="details">
            <div class="info-item">
                <i class="fas fa-file-alt"></i>
                <span>Documents validés et sécurisés</span>
            </div>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>Date de soumission: <span id="submissionDate"></span></span>
            </div>
            <div class="info-item">
                <i class="fas fa-check-circle"></i>
                <span>Numéro de référence: <span id="referenceNumber"></span></span>
            </div>
        </div>

        <p>Vous allez être redirigé vers la page d'accueil...</p>
        <div class="loading">
            <div class="loading-bar"></div>
        </div>

        <div class="buttons">
            <a href="https://www.inptic.ga" class="btn btn-primary">
                <i class="fas fa-home"></i> Page d'accueil
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimer confirmation
            </button>
        </div>
    </div>

    <script>
        // Générer un numéro de référence aléatoire
        function generateReference() {
            return 'REF-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        }

        // Formater la date actuelle
        function formatDate(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        }

        // Initialiser les informations
        document.getElementById('submissionDate').textContent = formatDate(new Date());
        document.getElementById('referenceNumber').textContent = generateReference();

        // Redirection après 5 secondes
        setTimeout(function() {
            window.location.href = "etudiant.php";
        }, 5000);

        // Animation de chargement fluide
        document.querySelector('.loading-bar').style.transition = 'width 10s linear';
    </script>
</body>
</html>
