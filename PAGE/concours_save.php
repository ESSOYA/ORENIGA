<<?php
session_start();
$host = 'localhost';
$dbname = 'oreniga';
$username = 'root';
$password = '';

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Récupérer l'ID de l'étudiant depuis la session
$etudiant_id = $_SESSION['etu_id'] ?? null;
$documents_exist = false;
$documents_status = false;
$documents_stat = false;
$documents = [];

// Vérifier si des documents existent déjà pour cet étudiant
if ($etudiant_id) {
    $stmt = $conn->prepare("SELECT * FROM documents_inptic WHERE etu_id = ?");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $documents = $result->fetch_assoc();
        $documents_exist = true;
    }
    $stmt->close();
}


if ($etudiant_id) {
    // Vérifier si un document pour cet étudiant a le statut "approuvé" dans la table documents
    $stmt = $conn->prepare("
        SELECT d.status 
        FROM documents d
        INNER JOIN documents_inptic di ON d.document_id = di.id
        WHERE di.etu_id = ? AND d.status = 'approuvé'
    ");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si un document avec le statut "approuvé" est trouvé
    if ($result->num_rows > 0) {
        $documents_status = true;
    } else {
        // Si aucun document approuvé n'est trouvé, récupérer les informations des documents existants
        $stmt = $conn->prepare("SELECT * FROM documents_inptic WHERE etu_id = ?");
        $stmt->bind_param("i", $etudiant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $documents = $result->fetch_assoc();
            $documents_status = false;
        }
    }
    $stmt->close();
}


if ($etudiant_id) {
    // Vérifier si un document pour cet étudiant a le statut "approuvé" dans la table documents
    $stmt = $conn->prepare("
        SELECT d.status 
        FROM documents d
        INNER JOIN documents_inptic di ON d.document_id = di.id
        WHERE di.etu_id = ? AND d.status = 'rejeté'
    ");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si un document avec le statut "approuvé" est trouvé
    if ($result->num_rows > 0) {
        $documents_stat = true;
    } else {
        // Si aucun document approuvé n'est trouvé, récupérer les informations des documents existants
        $stmt = $conn->prepare("SELECT * FROM documents_inptic WHERE etu_id = ?");
        $stmt->bind_param("i", $etudiant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $documents = $result->fetch_assoc();
            $documents_stat = false;
        }
    }
    $stmt->close();
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = "INPTIC/";
    $telephone = $_POST['telephone'];

    // Définir les noms de fichiers, en utilisant les fichiers existants si aucun nouveau fichier n'est téléchargé
    $fichier_bac = !empty($_FILES["fichier_bac"]["name"]) ? $uploadDir . basename($_FILES["fichier_bac"]["name"]) : ($documents['fichier_bac'] ?? '');
    $fichier_notes = !empty($_FILES["fichier_notes"]["name"]) ? $uploadDir . basename($_FILES["fichier_notes"]["name"]) : ($documents['fichier_notes'] ?? '');
    $fichier_naissance = !empty($_FILES["fichier_naissance"]["name"]) ? $uploadDir . basename($_FILES["fichier_naissance"]["name"]) : ($documents['fichier_naissance'] ?? '');
    $fichier_identite = !empty($_FILES["fichier_identite"]["name"]) ? $uploadDir . basename($_FILES["fichier_identite"]["name"]) : ($documents['fichier_identite'] ?? '');
    $photos_identite = !empty($_FILES["photos_identite"]["name"]) ? $uploadDir . basename($_FILES["photos_identite"]["name"]) : ($documents['photos_identite'] ?? '');

    // Déplacer les nouveaux fichiers vers le dossier de destination
    if (!empty($_FILES["fichier_bac"]["tmp_name"])) move_uploaded_file($_FILES["fichier_bac"]["tmp_name"], $fichier_bac);
    if (!empty($_FILES["fichier_notes"]["tmp_name"])) move_uploaded_file($_FILES["fichier_notes"]["tmp_name"], $fichier_notes);
    if (!empty($_FILES["fichier_naissance"]["tmp_name"])) move_uploaded_file($_FILES["fichier_naissance"]["tmp_name"], $fichier_naissance);
    if (!empty($_FILES["fichier_identite"]["tmp_name"])) move_uploaded_file($_FILES["fichier_identite"]["tmp_name"], $fichier_identite);
    if (!empty($_FILES["photos_identite"]["tmp_name"])) move_uploaded_file($_FILES["photos_identite"]["tmp_name"], $photos_identite);

    // Mise à jour ou insertion selon l'existence des documents
    if ($documents_exist) {
        $stmt = $conn->prepare("UPDATE documents_inptic SET fichier_bac=?, fichier_notes=?, fichier_naissance=?, fichier_identite=?, photos_identite=?, telephone=? WHERE etu_id=?");
        $stmt->bind_param("ssssssi", $fichier_bac, $fichier_notes, $fichier_naissance, $fichier_identite, $photos_identite, $telephone, $etudiant_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO documents_inptic (fichier_bac, fichier_notes, fichier_naissance, fichier_identite, photos_identite, telephone, etu_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $fichier_bac, $fichier_notes, $fichier_naissance, $fichier_identite, $photos_identite, $telephone, $etudiant_id);
    }

    if ($stmt->execute()) {
        echo "Les informations ont été mises à jour avec succès !";
    
        // Get the correct `document_id` from `documents_inptic`
        $doc_id_inptic = $documents['id'] ?? $conn->insert_id; // Ensure this ID exists in `documents_inptic`
    
        // Set current date and default status
        $current_date = date('Y-m-d');
        $default_status = 'traitement';
    
        if ($documents_exist) {
            // Update the existing document
            $stmt_update = $conn->prepare("UPDATE documents SET date = ?, status = ? WHERE document_id = ?");
            $stmt_update->bind_param("ssi", $current_date, $default_status, $doc_id_inptic);
    
            if ($stmt_update->execute()) {
                echo "Le document a été mis à jour avec succès !";
            } else {
                echo "Erreur lors de la mise à jour du document : " . $stmt_update->error;
            }
            $stmt_update->close();
    
        } else {
            // Insert a new document record
            $stmt_insert = $conn->prepare("INSERT INTO documents (document_id, date, status) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iss", $doc_id_inptic, $current_date, $default_status);
    
            if ($stmt_insert->execute()) {
                echo "Le document a été inséré avec succès !";
            } else {
                echo "Erreur lors de l'insertion du document : " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
    
        // Redirect to a success page
        header("Location: succes.php");
        exit;
    } else {
        echo "Erreur : " . $stmt->error;
    }
    $stmt->close();
    
}

$query = "SELECT 
    d.id AS document_id,
    d.document_id AS d_id,
    d.date AS document_date,
    d.status AS document_status,
    di.etu_id,
    di.fichier_bac,
    di.fichier_notes,
    di.fichier_naissance,
    di.fichier_identite,
    di.photos_identite,
    di.telephone,
    e.Nom_famille AS etudiant_Nom_famille,
    e.Prenom AS etudiant_Prenom,
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
    $fichier_bac,
    $fichier_notes,
    $fichier_naissance,
    $fichier_identite,
    $photos_identite,
    $telephone,
    $etudiant_nom_famille,
    $etudiant_prenom,
    $etudiant_email
);

// Regrouper les documents par étudiant
$students = [];
while ($stmt->fetch()) {
    if (!isset($students[$etu_id])) {
        $students[$etu_id] = [
            'etudiant_nom_famille' => $etudiant_nom_famille,
            'etudiant_prenom' => $etudiant_prenom,
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
        'telephone' => $telephone
    ];
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Téléversement de Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --background-color: #f8f9fa;
            --card-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        body {
            background: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2b2d42;
        }

        /* Animations keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .container {
            animation: fadeInUp 0.8s ease-out;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            position: relative;
        }

        h1::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: var(--accent-color);
            margin: 1rem auto;
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .card-body {
            padding: 2rem;
        }

        .status-badge {
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .document-card {
            height: 100%;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        }

        .upload-icon {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .upload-status {
            font-size: 0.9em;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: #f8f9fa;
            display: inline-block;
            margin-top: 1rem;
        }

        .document-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .small {
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .card-body {
                padding: 1.5rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1>Tableau de Bord - Dossier d'Inscription</h1>
        
        <!-- Statut global du dossier -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                    <?php foreach ($students as $etu_id => $student) { ?>
                        <h3 class="mb-4">Statut de votre dossier</h3>
                        <?php foreach ($student['documents'] as $document) { ?>
                           
                        <span class="badge bg-warning status-badge"><?php echo ucfirst($document['document_status']); ?></span>
                        <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                <?php if ($documents_status): ?>
                    <div class="card-body text-center">
                       <h2>Tous vos documents ont été approuvés !!!!!</h2> 
                       <button type="button" class="btn btn-primary btn-lg" onclick="window.location.href='FINALISE.php';">Continuer la procedure</button>
                    </div>
                    
                    <?php else: ?>
                        <?php if ($documents_exist): ?>
                           
                            <?php if ($documents_stat): ?>
                                <div class="card-body text-center">
                                    <h2>Vos  document ont été rejtés veuillez recommencer !</h2>
                                    <p>veuillez compléter votre dossier sinon il sera supprimer </p>
                                </div>
                                    
                                    <?php foreach ($students as $etu_id => $student) { ?>
                                        <?php foreach ($student['documents'] as $document) { ?>
                                            <div class="card-body">
                                            <h4 class="mb-4">Informations de contact</h4>
                        <div class="form-group">
                        <form id="upload-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                            <label for="phone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="telephone" placeholder="Format: 066 81 35 42 " value="<?php echo basename($document['telephone']); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents à téléverser -->
        <div class="row g-4">
            <!-- Les cartes de documents restent les mêmes mais avec le nouveau style -->
            <div class="col-md-4">
                
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Acte de naissance</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_naissance"  >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_naissance'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_naissance']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Attestation de reussite au BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_bac"  >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_bac'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_bac']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Relevé de notes du BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_notes" >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_notes'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_notes']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Piece d'identité</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_identite" >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_identite'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_identite']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-image upload-icon mb-3"></i>
                        <h5>Demi carte photo sur fond blanc</h5>
                        <input type="file" class="form-control mb-2" accept=".jpg" name="photos_identite" >
                        <small class="text-muted d-block mb-2">Format image uniquement</small>
                        <?php if (!empty($document['photos_identite'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['photos_identite']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                        
                    </div>
                </div>
            </div>
               

            <!-- Répéter pour les autres documents avec le même style -->
            <!-- ... Les autres cartes de documents ... -->

        </div>
       
        <!-- Bouton de soumission -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <button class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    Soumettre le dossier
                </button>
                
            </div>
</form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Le même JavaScript que précédemment
        document.getElementById('phone').addEventListener('input', function(e) {
            let phone = e.target.value.replace(/\D/g, '');
            if (phone.length === 9) {
                e.target.classList.add('is-valid');
                e.target.classList.remove('is-invalid');
            } else {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            }
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    let status = e.target.parentElement.querySelector('.upload-status');
                    status.classList.add('upload-success');
                    status.innerHTML = '<i class="fas fa-check text-success"></i> Fichier prêt';
                    status.classList.remove('text-warning');
                    status.classList.add('text-success');
                    
                    setTimeout(() => {
                        status.classList.remove('upload-success');
                    }, 500);
                }
            });
        });
    </script>

                                            <?php } ?>
                                            <?php } ?>

                                        <?php else: ?>
                                            <h3>Vous avez déjà téléversé vos documents. Vous pouvez les modifier ci-dessous.</h3>
                                            <p>veuillez complez votre dossier si il est incomplet sous peine de se faire supprimer le dossier !</p>
                                            <?php foreach ($students as $etu_id => $student) { ?>
                                        <?php foreach ($student['documents'] as $document) { ?>
                                            <div class="card-body">
                        <h4 class="mb-4">Informations de contact</h4>
                        <div class="form-group">
                        <form id="upload-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                            <label for="phone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="telephone" placeholder="Format: 066 81 35 42 " value="<?php echo basename($document['telephone']); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents à téléverser -->
        <div class="row g-4">
            <!-- Les cartes de documents restent les mêmes mais avec le nouveau style -->
            <div class="col-md-4">
                
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Acte de naissance</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_naissance"  >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_naissance'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_naissance']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Attestation de reussite au BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_bac"  >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_bac'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_bac']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Relevé de notes du BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_notes" >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_notes'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_notes']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Piece d'identité</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_identite" >
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <?php if (!empty($document['fichier_identite'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['fichier_identite']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-image upload-icon mb-3"></i>
                        <h5>Demi carte photo sur fond blanc</h5>
                        <input type="file" class="form-control mb-2" accept=".jpg" name="photos_identite" >
                        <small class="text-muted d-block mb-2">Format image uniquement</small>
                        <?php if (!empty($document['photos_identite'])): ?>
                                        <span class="text-info">Fichier actuel : <?php echo basename($document['photos_identite']); ?></span>
                                        <i class="fas fa-check text-success"></i> Fichier prêt'
                                    <?php else: ?>
                                        <span class="text-warning">Pas de fichier actuel</span>
                                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                                    <?php endif; ?>
                        
                    </div>
                </div>
            </div>
               

            <!-- Répéter pour les autres documents avec le même style -->
            <!-- ... Les autres cartes de documents ... -->

        </div>
       
        <!-- Bouton de soumission -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <button class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    Soumettre le dossier
                </button>
                
            </div>
</form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Le même JavaScript que précédemment
        document.getElementById('phone').addEventListener('input', function(e) {
            let phone = e.target.value.replace(/\D/g, '');
            if (phone.length === 9) {
                e.target.classList.add('is-valid');
                e.target.classList.remove('is-invalid');
            } else {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            }
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    let status = e.target.parentElement.querySelector('.upload-status');
                    status.classList.add('upload-success');
                    status.innerHTML = '<i class="fas fa-check text-success"></i> Fichier prêt';
                    status.classList.remove('text-warning');
                    status.classList.add('text-success');
                    
                    setTimeout(() => {
                        status.classList.remove('upload-success');
                    }, 500);
                }
            });
        });
    </script>

                                            <?php } ?>
                                            <?php } ?>

                                            <?php endif; ?>
                            <?php else: ?>
                                
                    <div class="card-body">
                        <h4 class="mb-4">Informations de contact</h4>
                        <div class="form-group">
                        <form id="upload-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                            <label for="phone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="telephone" placeholder="Format: 066 81 35 42 " required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents à téléverser -->
        <div class="row g-4">
            <!-- Les cartes de documents restent les mêmes mais avec le nouveau style -->
            <div class="col-md-4">
                
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Acte de naissance</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_naissance"  required>
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Attestation de reussite au BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_bac"  required>
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Relevé de notes du BAC</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_notes" required>
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <h5>Piece d'identité</h5>
                        <input type="file" class="form-control mb-2" accept=".pdf" name="fichier_identite" required>
                        <small class="text-muted d-block mb-2">Format PDF uniquement</small>
                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-body text-center">
                        <i class="fas fa-image upload-icon mb-3"></i>
                        <h5>Demi carte photo sur fond blanc</h5>
                        <input type="file" class="form-control mb-2" accept=".jpg" name="photos_identite" required>
                        <small class="text-muted d-block mb-2">Format image uniquement</small>
                        <span class="upload-status text-warning"><i class="fas fa-clock"></i> En attente</span>
                    </div>
                </div>
            </div>
               

            <!-- Répéter pour les autres documents avec le même style -->
            <!-- ... Les autres cartes de documents ... -->

        </div>
       
        <!-- Bouton de soumission -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <button class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    Soumettre le dossier
                </button>
                
            </div>
</form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Le même JavaScript que précédemment
        document.getElementById('phone').addEventListener('input', function(e) {
            let phone = e.target.value.replace(/\D/g, '');
            if (phone.length === 9) {
                e.target.classList.add('is-valid');
                e.target.classList.remove('is-invalid');
            } else {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            }
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    let status = e.target.parentElement.querySelector('.upload-status');
                    status.classList.add('upload-success');
                    status.innerHTML = '<i class="fas fa-check text-success"></i> Fichier prêt';
                    status.classList.remove('text-warning');
                    status.classList.add('text-success');
                    
                    setTimeout(() => {
                        status.classList.remove('upload-success');
                    }, 500);
                }
            });
        });
    </script>
    
    <?php endif; ?>
    <?php endif; ?>
</body>
</html>