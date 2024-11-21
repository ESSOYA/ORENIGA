<?php
session_start();
include 'db_connection.php'; // Fichier de connexion à la base de données

// ID de l'utilisateur connecté et ID de l'autre utilisateur (statique pour l'exemple)
$_SESSION['etu_id'] = 1; // ID de l'étudiant connecté
$receiver_id = 19; // ID de l'autre étudiant

// Gérer l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $sender_id = $_SESSION['user']['etu_id'];

    // Insertion du message dans la base de données
    $stmt = $conn->prepare("INSERT INTO dm (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Récupération des messages
$stmt = $conn->prepare("SELECT * FROM dm WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp");
$stmt->bind_param("iiii", $_SESSION['etu_id'], $receiver_id, $receiver_id, $_SESSION['etu_id']);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface avec Support Emoji</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-button@4/dist/index.min.css">
    <style>
        /* CSS styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #12c2e9, #c471ed, #f64f59);
        }
        .chat-container {
            display: flex;
            width: 90%;
            height: 90vh;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: #fff;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            
            border-radius: 15px;
            backdrop-filter: blur(15px);
        }
        .chat-sidebar {
            width: 25%;
            background-color: #151616;
            padding: 20px;
            box-sizing: border-box;
            border-right: 1px solid #ddd;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px;
            background-color: #151616;
            color: #fff;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }
        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #fafafa;
            background: linear-gradient(135deg, #22da68, #149e4e, #6b030a);
           
        }
        .chat-footer {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f0f0f0;
            border-top: 1px solid #ddd;
            background-color: #151616;
        }
        #emoji-btn {
            font-size: 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            margin-right: 8px;
        }
        #message-input {
            
            padding: 20px;
            width:700px;
            border-radius: 20px;
            border: 1px solid #ccc;
            margin-right: 8px;
            background-color: #151616;
            color: #fff;
        }
        #search {
            
            padding: 10px;
            width:100%;
            border-radius: 20px;
            border: 1px solid #ccc;
            margin-right: 8px;
        }
        #send-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            cursor: pointer;
        }
        #send-btn:hover {
            background-color: #45a049;
        }

        .online-friends {
            width: 350px;
            padding: 2px;
            color: #fff;

           
           
        }

        .online-friends h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #fcefef;
        }

        .online-friend {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .online-friend img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1px;
        }

        .btn-external{
            margin-right: 90px;
            margin-top: 100PX;
            background: #07c911;
            background-color: rgb(255, 255, 255);
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 1px;
            width: 100px;
            border: none;
            border-radius: 18px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            font-size: MEDIUM;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        
        <!-- Sidebar section -->
        <div class="chat-sidebar">
            <input type="text" id="search" placeholder="Search or start new chat">
            <div class="online-friends"> <br> <br>
        
        <?php
        // Requête pour récupérer tous les utilisateurs
        $sql = "SELECT Nom_famille, prénom, photo_profil FROM etudiants ORDER BY etu_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        // Boucle pour afficher chaque utilisateur
        while ($row = $result->fetch_assoc()) {
            echo '<div class="contact">';
            echo '<a href="index.html"><img src="' . htmlspecialchars($row["photo_profil"]) . '" alt="404" width="40" height="40"></a>';
            echo '<strong></strong> ' . htmlspecialchars($row["Nom_famille"]) . '';
            echo '<strong></strong> ' . htmlspecialchars($row["prénom"]) . '<br>';
            echo '</div><br>';
        }

        $stmt->close();
        ?>
        
    </div>
    <a href="DASH.php"  class="external-link">
                            <button class="btn-external">RETOUR</button>
                        </a>
        </div>
        
        <!-- Main chat section -->
        <div class="chat-main">
            <div class="chat-header">Conversation avec l'Étudiant ID: <?php echo $receiver_id; ?></div>
            
            <!-- Chat messages section -->
            <div class="chat-body">
                <?php foreach ($messages as $msg): ?>
                    <div>
                        <strong><?php echo $msg['sender_id'] == $_SESSION['etu_id'] ? 'Vous' : 'Autre Étudiant'; ?>:</strong>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Footer with emoji and text input -->
            <div class="chat-footer">
                <form action="" method="POST">
                    <button type="button" id="emoji-btn">😊</button>
                    <input type="text" id="message-input" name="message" placeholder="Type a message" required>
                    <button type="submit" id="send-btn">Envoyer</button>
                </form>
            </div>
        </div>
        
    </div> 
   

    <!-- Emoji Button library -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-button@4/dist/index.min.js"></script>
    <script>
        // Initialize the Emoji Button library
        const picker = new EmojiButton();
        const emojiButton = document.querySelector('#emoji-btn');
        const messageInput = document.querySelector('#message-input');

        // Toggle emoji picker when the emoji button is clicked
        emojiButton.addEventListener('click', () => {
            picker.togglePicker(emojiButton);
        });

        // Insert selected emoji into the input field
        picker.on('emoji', emoji => {
            messageInput.value += emoji;
        });
    </script>
</body>
</html>
