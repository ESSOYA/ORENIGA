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

// Préparation de la requête SQL
$query = "SELECT 
    d.id AS document_id,
    d.document_id AS d_id,
    d.date AS document_date,
    d.status AS document_status,
    di.etu_id,
    di.filiere,
    di.fichier_bac,
    di.fichier_notes,
    di.fichier_naissance,
    di.fichier_identite,
    di.photos_identite,
    di.telephone,
    e.Nom_famille AS etudiant_Nom_famille,
    e.Prenom AS etudiant_Prenom,
    e.filiere AS filiere,
    e.BAC AS BAC,
    e.Email AS etudiant_Email
FROM 
    documents d
JOIN 
    documents_inptic di ON d.document_id = di.id
JOIN 
    etudiant e ON di.etu_id = e.etu_id";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Erreur lors de la préparation de la requête : " . $conn->error);
}
$stmt->execute();

// Lier les résultats
$stmt->bind_result(
    $document_id,
    $d_id,
    $document_date,
    $document_status,
    $etu_id,
    $filiere,
    $fichier_bac,
    $fichier_notes,
    $fichier_naissance,
    $fichier_identite,
    $photos_identite,
    $telephone,
    $etudiant_nom_famille,
    $etudiant_prenom,
    $filier,
    $BAC,
    $etudiant_email
);

// Regrouper les documents par étudiant
$students = [];
while ($stmt->fetch()) {
    if (!isset($students[$etu_id])) {
        $students[$etu_id] = [
            'etudiant_nom_famille' => $etudiant_nom_famille,
            'etudiant_prenom' => $etudiant_prenom,
            'filiere' => $filier,
            'BAC' => $BAC,
            'etudiant_email' => $etudiant_email,
            'documents' => []
        ];
    }
    $students[$etu_id]['documents'][] = [
        'document_id' => $document_id,
        'd_id' => $d_id,
        'document_date' => $document_date,
        'document_status' => $document_status,
        'fichier_bac' => $fichier_bac,
        'fichier_notes' => $fichier_notes,
        'fichier_naissance' => $fichier_naissance,
        'fichier_identite' => $fichier_identite,
        'photos_identite' => $photos_identite,
        'telephone' => $telephone,
        'filiere' => $filiere
    ];
}

// Fermer la requête et la connexion
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Dossiers</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .student {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .student h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .student p {
            margin: 5px 0;
            color: #666;
        }
        .document {
            background-color: #fafafa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .document p {
            margin: 8px 0;
        }
        .btn {
            padding: 8px 15px;
            margin: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-approve {
            background-color: #4CAF50;
            color: white;
        }
        .btn-reject {
            background-color: #f44336;
            color: white;
        }
        .btn-delete {
            background-color: #9E9E9E;
            color: white;
        }
        .btn-view {
            background-color: #2196F3;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }

        .btn-approve-all {
    background-color: #4CAF50;
    color: white;
    padding: 8px 15px;
    margin: 5px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
.btn-reject-all {
    background-color: #f44336;
    color: white;
    padding: 8px 15px;
    margin: 5px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
.search-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container input {
            padding: 10px;
            width: 60%;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        #statusBox {
    position: RELATIVE;
    top: 2px;
    right: 2x;
    left: 2000x;
    width: 250px;
    padding: 15px;
    background-color: #f9f9f9;
    border: 2px solid #007bff; /* Couleur de la bordure pour la visibilité */
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    font-family: Arial, sans-serif;
    z-index: 1000; /* S'assure que le cadre reste visible au-dessus des autres éléments */
}

#statusBox h3 {
    margin-top: 0;
    font-size: 1.7em;
    color: #007bff;
}

#statusContent {
    font-size: 1.5em;
    color: #e60303;
    font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
}

.filter-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }
        .filter-all { background-color: #333; }
        .filter-approved { background-color: #4CAF50; }
        .filter-rejected { background-color: #f44336; }
        .filter-pending { background-color: #FF9800; }
        .back-button {
            padding: 10px 20px;
            margin-bottom: 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #007bff;
            text-align: center;
            display: inline-block;
            font-size: 16px;
            text-decoration: none;
        }

        .btn-download {
        
       
background-color: #2196F3;
        color: white;
    }
    .btn-delete-all {
    background-color: #360808; /* Orange foncé pour la suppression */
    color: white;
    margin-top: 10px;
}

.btn-delete-all:hover {
    background-color: #103a06;
}

.preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #ddd;
            padding: 5px;
            border-radius: 10px;
        }
    
    </style>
</head>
<body>

    <h1>Gestion des Dossiers Étudiants</h1>
    <div class="search-container">
        <input type="text" id="searchBar" onkeyup="searchStudent()" placeholder="Rechercher un étudiant..." title="Rechercher un étudiant">
    </div>
    <div class="filter-buttons">
        <button class="filter-button filter-all" onclick="filterStatus('all')">Tous</button>
        <button class="filter-button filter-approved" onclick="filterStatus('approuvé')">Approuvé</button>
        <button class="filter-button filter-rejected" onclick="filterStatus('rejeté')">Rejeté</button>
        <button class="filter-button filter-pending" onclick="filterStatus('traitement')">En attente</button>
    </div>
    <?php foreach ($students as $etu_id => $student) { ?>
        <div class="student">
            <h2><?php echo $student['etudiant_nom_famille'] . ' ' . $student['etudiant_prenom']; ?></h2>
            <p>Email : <?php echo $student['etudiant_email']; ?></p>
          
            <h2>FILIERE  : <?php echo $student['filiere'] ; ?></h2>
            <h2>Baccalauréat : <?php echo $student['BAC'] ; ?></h2>
            <h3>Documents :</h3>
            <?php foreach ($student['documents'] as $document) { ?>
                <div class="document">
                <div id="statusBox">
    <h3>STATUT DOSSIER :</h3>
    <p id="statusContent"><?php echo ucfirst($document['document_status']); ?></p>
</div>

                    <p><strong>Date du Document :</strong> <?php echo $document['document_date']; ?></p>
                    <p><strong>Numéro de téléphone :</strong> <?php echo $document['telephone']; ?></p>
                 

                    <h4>Fichiers :</h4>

                    <div class="document" >
                        <!-- Fichier Bac -->
                    <?php if ($document['fichier_bac']) { ?>
                        <p>1) <strong>ATTESTATION DU BAC : </strong>
                            <a href="<?php echo $document['fichier_bac']; ?>" class="btn btn-view" target="_blank">Voir</a>
                            <button class="btn btn-delete" onclick="handleAction('delete', <?php echo $document['d_id']; ?>, 'fichier_bac')">Supprimer</button>
                            <a href="download.php?file=<?php echo urlencode($document['fichier_bac']); ?>&name=<?php echo urlencode($student['etudiant_nom_famille'] . '_' . $student['etudiant_prenom'] . '_BAC'); ?>" class="btn btn-download">Télécharger</a>
                        </p>
                    <?php } ?>

                    </div>
                    <div class="document">
                        <!-- Fichier Notes -->
                    <?php if ($document['fichier_notes']) { ?>
                        <p>2) <strong>Relevé de notes du BAC  : </strong>
                            <a href="<?php echo $document['fichier_notes']; ?>" class="btn btn-view" target="_blank">Voir</a>
                            <button class="btn btn-delete" onclick="handleAction('delete', <?php echo $document['d_id']; ?>, 'fichier_notes')">Supprimer</button>
                            <a href="download.php?file=<?php echo urlencode($document['fichier_notes']); ?>&name=<?php echo urlencode($student['etudiant_nom_famille'] . '_' . $student['etudiant_prenom'] . '_Notes'); ?>" class="btn btn-download">Télécharger</a>
                        </p>
                    <?php } ?>
                    </div>

                   <div class="document">
                     <!-- Fichier Naissance -->
                     <?php if ($document['fichier_naissance']) { ?>
                        <p>3)  <strong> Acte de Naissance : </strong>
                            <a href="<?php echo $document['fichier_naissance']; ?>" class="btn btn-view" target="_blank">Voir</a>
                            
                            <button class="btn btn-delete" onclick="handleAction('delete', <?php echo $document['d_id']; ?>, 'fichier_naissance')">Supprimer</button>
                            <a href="download.php?file=<?php echo urlencode($document['fichier_naissance']); ?>&name=<?php echo urlencode($student['etudiant_nom_famille'] . '_' . $student['etudiant_prenom'] . '_naissance'); ?>" class="btn btn-download">Télécharger</a>
                        </p>
                    <?php } ?>
                   </div>

                    <div class="document">
                        <!-- Fichier Identité -->
                    <?php if ($document['fichier_identite']) { ?>
                        <p>4) <strong>Piece d'identité : </strong>
                            <a href="<?php echo $document['fichier_identite']; ?>" class="btn btn-view" target="_blank">Voir</a>
                            <button class="btn btn-delete" onclick="handleAction('delete', <?php echo $document['d_id']; ?>, 'fichier_identite')">Supprimer</button>
                            <a href="download.php?file=<?php echo urlencode($document['fichier_identite']); ?>&name=<?php echo urlencode($student['etudiant_nom_famille'] . '_' . $student['etudiant_prenom'] . '_identite'); ?>" class="btn btn-download">Télécharger</a>
                        </p>
                    <?php } ?>
                    </div>

                   <div class="document">
                     <!-- Photo Identité -->
                     <?php if ($document['photos_identite']) { ?>
                        <p>5) <strong>Photo Identité : </strong>
                        <div class="preview-container">
    <h2>Prévisualisation dela demi carte photo</h2>
    
    <!-- Vérifie si l'URL de l'image est définie et existe -->
    <?php if (!empty($document['photos_identite']) && file_exists($document['photos_identite'])): ?>
        <img id="imagePreview" class="preview-image" src="<?php echo $document['photos_identite']; ?>" alt="Aperçu de l'image">
    <?php else: ?>
        <p>Aucune image trouvée pour cet étudiant.</p>
    <?php endif; ?>
</div>

                            <a href="<?php echo $document['photos_identite']; ?>" class="btn btn-view" target="_blank">Voir</a>
                            <button class="btn btn-delete" onclick="handleAction('delete', <?php echo $document['d_id']; ?>, 'photos_identite')">Supprimer</button>
                            <a href="download.php?file=<?php echo urlencode($document['photos_identite']); ?>&name=<?php echo urlencode($student['etudiant_nom_famille'] . '_' . $student['etudiant_prenom'] . '_photos_identite'); ?>" class="btn btn-download">Télécharger</a>
                        </p>
                    <?php } ?>
                   </div>
                </div>
            <?php } ?>
            <!-- Bouton pour approuver tout le dossier -->
<button class="btn-approve-all" onclick="approveAllDocuments('<?php echo $etu_id; ?>')">Approuver tout le dossier</button>
<!-- Bouton pour rejeter tout le dossier -->
<button class="btn-reject-all" onclick="rejectAllDocuments('<?php echo $etu_id; ?>')">Rejeter tout le dossier</button>
<button class="btn btn-delete-all" onclick="handleDeleteAll(<?php echo $document['d_id']; ?>)">Supprimer le dossier</button>


<p><strong>Statut :</strong> <?php echo ucfirst($document['document_status']); ?></p>
        </div> <br><br>
    <?php } ?>

    <script>
        document.getElementById('fileInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

         // Fonction pour filtrer les étudiants en fonction du statut de leurs documents
         function filterStatus(status) {
            var students = document.getElementsByClassName("student");

            for (var i = 0; i < students.length; i++) {
                // Récupérer tous les statuts de document pour chaque étudiant
                var documentStatus = students[i].querySelectorAll("#statusContent");

                // Convertir les statuts en texte
                var statuses = Array.from(documentStatus).map(function(stat) {
                    return stat.innerText.toLowerCase();
                });

                // Logique de filtrage selon le statut sélectionné
                if (status === 'all') {
                    students[i].style.display = "";  // Afficher tous les étudiants
                } else if (statuses.includes(status)) {
                    students[i].style.display = "";  // Afficher les étudiants avec le statut sélectionné
                } else {
                    students[i].style.display = "none";  // Masquer les autres étudiants
                }
            }
        }
        function searchStudent() {
    // Récupère la valeur entrée dans la barre de recherche et la met en minuscule
    var input = document.getElementById("searchBar").value.toLowerCase();
    
    // Sélectionne tous les éléments qui contiennent des informations d'étudiants
    var students = document.getElementsByClassName("student");

    // Parcours chaque élément étudiant pour vérifier s'il correspond à la recherche
    for (var i = 0; i < students.length; i++) {
        // Récupère le texte du nom complet (nom + prénom) de l'étudiant
        var studentName = students[i].getElementsByTagName("h2")[0].innerText.toLowerCase();
        
        // Vérifie si le nom de l'étudiant contient le texte de la barre de recherche
        if (studentName.includes(input)) {
            students[i].style.display = ""; // Affiche l'étudiant si le texte correspond
        } else {
            students[i].style.display = "none"; // Masque l'étudiant si le texte ne correspond pas
        }
    }
}
    
   // Fonction pour rejeter tout le dossier d'un étudiant
function rejectAllDocuments(etuId) {
    if (confirm("Êtes-vous sûr de vouloir rejeter tout le dossier de cet étudiant ?")) {
        window.location.href = `process_action.php?action=reject_all&etu_id=${etuId}`;
    }
}

// Fonction pour approuver tout le dossier d'un étudiant
function approveAllDocuments(etuId) {
    if (confirm("Êtes-vous sûr de vouloir approuver tout le dossier de cet étudiant ?")) {
        window.location.href = `process_action.php?action=approve_all&etu_id=${etuId}`;
    }
}


function handleAction(action, d_id, file_type) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
        // Construction de l'URL avec les bons paramètres
        var url = "delete_document.php?action=" + action + "&document_id=" + d_id + "&file_type=" + file_type;

        // Redirection vers le script PHP
        window.location.href = url;
    }
}
function handleDeleteAll(d_id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer toute la ligne ? Cette action est irréversible.')) {
        // Redirection vers le script PHP de suppression avec l'action et l'ID
        window.location.href = "delete_all_documents.php?action=delete_all&document_id=" + d_id;
    }
}



    </script>
<a href="personel.php" class="back-button">Retourner au Menu</a>
</body>
</html>
