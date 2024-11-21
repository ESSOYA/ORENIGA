<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = $_POST['contenu'];
    $imageUrl = null;
    $videoUrl = null;

    // Gestion des fichiers
    if (!empty($_FILES['image']['name'])) {
        $imageUrl = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imageUrl);
    }

    if (!empty($_FILES['video']['name'])) {
        $videoUrl = 'uploads/' . basename($_FILES['video']['name']);
        move_uploaded_file($_FILES['video']['tmp_name'], $videoUrl);
    }

    // Insertion dans la base de données
    $stmt = $pdo->prepare("INSERT INTO publications (auteur, contenu, image_url, video_url, date_publication) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute(['Auteur Exemple', $contenu, $imageUrl, $videoUrl]);

    header('Location: index.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instamini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            --primary-color: #0095f6;
            --secondary-color: #e4e6eb;
            --background-color: #fafafa;
            --border-color: #dbdbdb;
            --text-color: #262626;
            --shadow: 0 1px 2px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--background-color);
            min-height: 100vh;
            color: var(--text-color);
        }

        .navbar {
            background-color: white;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            max-width: 935px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-color);
            text-decoration: none;
            font-family: 'Instagram Sans', cursive;
        }

        .main-container {
            max-width: 600px;
            margin: 80px auto 20px auto;
            padding: 20px;
        }

        .post-creator {
            background: white;
            padding: 20px;
            border-radius: 3px;
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
        }

        .post-input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            margin-bottom: 15px;
            resize: none;
            font-size: 14px;
            min-height: 100px;
        }

        .post-input:focus {
            outline: none;
            border-color: #8e8e8e;
        }

        .post-actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .media-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .btn:hover {
            opacity: 0.8;
        }

        .post {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            margin-bottom: 24px;
            animation: fadeIn 0.5s ease;
        }

        .post-header {
            display: flex;
            align-items: center;
            padding: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .post-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-info {
            flex-grow: 1;
        }

        .post-author {
            font-weight: 600;
            font-size: 14px;
        }

        .post-time {
            font-size: 12px;
            color: #8e8e8e;
        }

        .post-content {
            padding: 12px 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .post-image, .post-video {
            width: 100%;
            max-height: 600px;
            object-fit: cover;
        }

        .post-actions-bar {
            padding: 8px 14px;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 16px;
        }

        .post-action {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: var(--text-color);
            padding: 8px;
        }

        .post-action:hover {
            opacity: 0.7;
        }

        .media-preview {
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 3px;
            overflow: hidden;
        }

        #imagePreview, #videoPreview {
            max-width: 100%;
            display: none;
        }

        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="#" class="logo">Instamini</a>
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau
            </button>
        </div>
    </nav>

    <div class="main-container">
        <div class="post-creator">
            <textarea class="post-input" placeholder="Partagez quelque chose..."></textarea>
            <div class="media-preview">
                <img id="imagePreview" src="" alt="Preview">
                <video id="videoPreview" controls>
                    Votre navigateur ne supporte pas la lecture de vidéos.
                </video>
            </div>
            <div class="post-actions">
                <div class="media-buttons">
                    <button class="btn btn-secondary" id="imageButton">
                        <i class="fas fa-image"></i> Photo
                    </button>
                    <button class="btn btn-secondary" id="videoButton">
                        <i class="fas fa-video"></i> Vidéo
                    </button>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;">
                    <input type="file" id="videoInput" accept="video/*" style="display: none;">
                </div>
                <button class="btn btn-primary" id="publishButton">
                    <i class="fas fa-paper-plane"></i> Publier
                </button>
            </div>
        </div>

        <div class="feed">
            <!-- Les publications apparaîtront ici -->
        </div>
    </div>

    <script>
        let selectedImage = null;
        let selectedVideo = null;

        document.getElementById('imageButton').addEventListener('click', () => {
            document.getElementById('imageInput').click();
        });

        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                selectedImage = file;
                selectedVideo = null;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const videoPreview = document.getElementById('videoPreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    videoPreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('videoButton').addEventListener('click', () => {
            document.getElementById('videoInput').click();
        });

        document.getElementById('videoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                selectedVideo = file;
                selectedImage = null;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('videoPreview');
                    const imagePreview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    imagePreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('publishButton').addEventListener('click', async function() {
            const input = document.querySelector('.post-input');
            const content = input.value.trim();
            
            if (content || selectedImage || selectedVideo) {
                this.disabled = true;
                const loadingSpinner = document.createElement('span');
                loadingSpinner.className = 'loading';
                this.appendChild(loadingSpinner);

                await new Promise(resolve => setTimeout(resolve, 1000));

                const feed = document.querySelector('.feed');
                const newPost = document.createElement('div');
                newPost.className = 'post';

                let mediaHTML = '';
                if (selectedImage) {
                    const imageUrl = document.getElementById('imagePreview').src;
                    mediaHTML = `<img src="${imageUrl}" alt="Publication image" class="post-image">`;
                } else if (selectedVideo) {
                    const videoUrl = document.getElementById('videoPreview').src;
                    mediaHTML = `
                        <video controls class="post-video">
                            <source src="${videoUrl}" type="${selectedVideo.type}">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    `;
                }

                newPost.innerHTML = `
                    <div class="post-header">
                        <img src="https://via.placeholder.com/32" alt="Avatar" class="post-avatar">
                        <div class="post-info">
                            <div class="post-author">Vous</div>
                            <div class="post-time">À l'instant</div>
                        </div>
                    </div>
                    ${mediaHTML}
                    <div class="post-content">${content}</div>
                    <div class="post-actions-bar">
                        <button class="post-action"><i class="far fa-heart"></i></button>
                        <button class="post-action"><i class="far fa-comment"></i></button>
                        <button class="post-action"><i class="far fa-paper-plane"></i></button>
                    </div>
                `;

                feed.insertBefore(newPost, feed.firstChild);
                
                input.value = '';
                selectedImage = null;
                selectedVideo = null;
                document.getElementById('imagePreview').style.display = 'none';
                document.getElementById('videoPreview').style.display = 'none';
                
                this.removeChild(loadingSpinner);
                this.disabled = false;
            }
        });

        const textarea = document.querySelector('.post-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>
