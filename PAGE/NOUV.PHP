
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
    $phone = $conn->real_escape_string($_POST['phone']);
    $genre = $conn->real_escape_string($_POST['gender']);
    $role = $conn->real_escape_string($_POST['role']);
    $bac = $conn->real_escape_string($_POST['bac']);
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
            $sql = "INSERT INTO etudiant (Nom_famille, Prenom, Email, mot_de_passe,  Téléphone, Genre, BAC) VALUES ('$firstname', '$prenom', '$email', '$password','$phone', '$genre', '$bac')";
        } elseif ($role == "ENSEIGNANT") {
            $sql = "INSERT INTO enseignant (Nom_famille, Prenom, Email, mot_de_passe,  Téléphone, Genre) VALUES ('$firstname', '$prenom', '$email', '$password','$phone', '$genre')";
        } elseif ($role == "ENTREPRISE") {
            $sql = "INSERT INTO entreprise (Nom_famille, Prenom, Email, mot_de_passe,  Téléphone, Genre) VALUES ('$firstname', '$prenom', '$email', '$password','$phone', '$genre')";
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