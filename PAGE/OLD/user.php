<?php
// Informations de connexion
$host = 'localhost';
$database = 'orenigo';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requête pour récupérer toutes les données de la table
$sql = "SELECT * FROM etudiants ORDER BY etu_id ASC";
$result = $conn->query($sql);

// Vérifier si des données ont été trouvées
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Nom de famille</th><th>Prénom</th><th>Nom d'utilisateur</th><th>Email</th><th>Téléphone</th><th>Genre</th><th>Photo de profil</th></tr>";
    
    // Afficher les données dans un tableau HTML
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["Nom_famille"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["prénom"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["téléphone"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["genre"]) . "</td>";
        
        // Affichage de l'image de profil
        echo "<td><img src='" . htmlspecialchars($row["photo_profil"]) . "' alt='Photo de profil' width='100' height='100'></td>";
        
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Aucune donnée trouvée.";
}

// Fermer la connexion
$conn->close();
?>
