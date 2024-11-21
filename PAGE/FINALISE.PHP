
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Inscription - ORENIGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(35,166,213,0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(35,166,213,0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(35,166,213,0); }
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #2193b0, #6dd5ed, #23a6d5, #4e65ff);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 1000px;
            margin: 20px;
            padding: 20px;
            perspective: 1000px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-out;
        }

        .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 60px;
            margin-bottom: 40px;
        }

        .logo {
            width: 200px;
            height: 200px;
            object-fit: contain;
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.4));
            background: rgba(255,255,255,0.15);
            padding: 20px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.1) rotate(5deg);
            background: rgba(255,255,255,0.25);
        }

        .title {
            font-size: 38px;
            margin: 20px 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .payment-box {
            background: rgba(255,255,255,0.95);
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            backdrop-filter: blur(20px);
            transform: translateY(0) rotateX(0);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .payment-box:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }

        .payment-header {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }

        .airtel-logo {
            width: 140px;
            margin-right: 30px;
            animation: pulse 2s infinite;
        }

        .form-group {
            margin-bottom: 30px;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.3s; }
        .form-group:nth-child(2) { animation-delay: 0.6s; }

        label {
            display: block;
            margin-bottom: 12px;
            color: #333;
            font-weight: 600;
            font-size: 1.1em;
        }

        input {
            width: 100%;
            padding: 18px;
            border: 2px solid #e1e1e1;
            border-radius: 15px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        input:focus {
            border-color: #4e65ff;
            box-shadow: 0 0 20px rgba(78,101,255,0.15);
            outline: none;
            background: white;
        }

        .btn-pay {
            background: linear-gradient(45deg, #4e65ff, #23a6d5);
            color: white;
            padding: 20px 30px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
        }

        .btn-pay:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255,255,255,0.3),
                transparent
            );
            transition: 0.5s;
        }

        .btn-pay:hover:before {
            left: 100%;
        }

        .btn-pay:hover {
            background: linear-gradient(45deg, #3f52cc, #1f95bf);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(78,101,255,0.3);
        }

        .secure-info {
            text-align: center;
            margin-top: 40px;
            color: #555;
            background: rgba(255,255,255,0.9);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(78,101,255,0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .secure-info p {
            margin: 10px 0;
            font-size: 1em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .secure-info i {
            color: #4e65ff;
        }

        .secure-info p:first-child {
            font-weight: 700;
            color: #4e65ff;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 10px;
            }

            .logos {
                flex-direction: column;
                gap: 30px;
            }

            .logo {
                width: 160px;
                height: 160px;
            }

            .payment-box {
                padding: 30px;
            }

            .title {
                font-size: 28px;
            }

            .payment-header {
                flex-direction: column;
                text-align: center;
            }

            .airtel-logo {
                margin: 0 0 20px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logos">
                <img src="ORENIGA.PNG" alt="Logo ORENIGA avec chapeau de diplômé" class="logo">
                <img src="LOGOINPTIC.PNG" alt="Logo INPTIC" class="logo">
            </div>
            <h1 class="title">Finalisation de votre inscription</h1>
            <p>Pour continuer votre inscription, veuillez procéder au paiement</p>
        </div>

        <div class="payment-box">
            <div class="payment-header">
                <img src="MONEY.PNG" alt="Logo Airtel Money" class="airtel-logo">
                <h2>Paiement par Airtel Money</h2>
            </div>

            <form id="payment-form">
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Numéro de téléphone Airtel</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{9}" placeholder="Exemple: 077123456" required>
                </div>

                <div class="form-group">
                    <label for="amount"><i class="fas fa-money-bill-wave"></i> Montant à payer</label>
                    <input type="text" id="amount" value="25000 FCFA" readonly>
                </div>

                <button type="submit" class="btn-pay">
                    <i class="fas fa-lock"></i> Payer maintenant
                </button>
            </form>

            <div class="secure-info">
                <p><i class="fas fa-shield-alt"></i> Paiement sécurisé par Airtel Money</p>
                <p><i class="fas fa-sms"></i> Une fois le paiement effectué, vous recevrez une confirmation par SMS</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const phone = document.getElementById('phone').value;
            if(phone.length === 9 && /^[0-9]+$/.test(phone)) {
                alert('Redirection vers la plateforme de paiement Airtel Money...');
                // Ici, vous pouvez ajouter la logique de redirection vers l'API Airtel Money
            } else {
                alert('Veuillez entrer un numéro de téléphone valide');
            }
        });

        // Add hover effect to payment box
        const paymentBox = document.querySelector('.payment-box');
        paymentBox.addEventListener('mousemove', (e) => {
            const rect = paymentBox.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xRotation = ((y - rect.height / 2) / rect.height) * 10;
            const yRotation = ((x - rect.width / 2) / rect.width) * 10;
            
            paymentBox.style.transform = `perspective(1000px) rotateX(${-xRotation}deg) rotateY(${yRotation}deg) translateZ(20px)`;
        });

        paymentBox.addEventListener('mouseleave', () => {
            paymentBox.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
        });
    </script>
</body>
</html>