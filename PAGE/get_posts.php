<?php
// Connection à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "votre_base_de_donnees";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération des posts
$sql = "
    SELECT 'admin' AS user_type, admin_id AS user_id, nom AS author, contenu AS content, date_post 
    FROM admin_posts 
    UNION ALL
    SELECT 'enseignant', ens_id, nom, contenu, date_post FROM enseignant_posts
    UNION ALL
    SELECT 'etudiant', etu_id, nom, contenu, date_post FROM etudiant_posts
    UNION ALL
    SELECT 'entreprise', entre_id, nom, contenu, date_post FROM entreprise_posts
    UNION ALL
    SELECT 'personnel', pers_id, nom, contenu, date_post FROM personnel_posts
    ORDER BY date_post DESC
";
$result = $conn->query($sql);

$posts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            "user_type" => $row["user_type"],
            "user_id" => $row["user_id"],
            "author" => $row["author"],
            "content" => $row["content"],
            "date" => $row["date_post"]
        ];
    }
}

echo json_encode($posts);

$conn->close();
?>
