<?php
$host = 'localhost';
$dbname = 'orenigo';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $conn->real_escape_string($_POST['famille']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $date = $conn->real_escape_string($_POST['dob']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $genre = $conn->real_escape_string($_POST['gender']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Préparation de la requête SQL en fonction du rôle
    $sql = null;
    if ($role == "ETUDIANT") {
        $sql = "INSERT INTO etudiants (Nom_famille, prénom, username, email, mot_de_passe, DATE_NAISSANCE, téléphone, genre) VALUES ('$firstname', '$prenom', '$username', '$email', '$password', '$date', '$phone', '$genre')";
    } elseif ($role == "ENSEIGNANT") {
        $sql = "INSERT INTO enseignants (Nom_famille, prénom, username, email, mot_de_passe, DATE_NAISSANCE, téléphone, genre) VALUES ('$firstname', '$prenom', '$username', '$email', '$password', '$date', '$phone', '$genre')";
    } elseif ($role == "ENTREPRISE") {
        $sql = "INSERT INTO entreprises (Nom_famille, prénom, username, email, mot_de_passe, DATE_NAISSANCE, téléphone, genre) VALUES ('$firstname', '$prenom', '$username', '$email', '$password', '$date', '$phone', '$genre')";
    }
    

    if ($sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                if (confirm('Confirmez-vous l\'inscription de $firstname $prenom avec le rôle de $role?')) {
                    window.location.href = 'ADMIN.php';
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

$conn->close();
?>