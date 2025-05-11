<?php
include 'db.php';
session_start();

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Génération du token CSRF s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération des informations de l'utilisateur avec une requête préparée
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialisation des variables
$success_message = "";
$error_message = "";

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Erreur de validation du formulaire. Veuillez réessayer.";
    } 
    // Mise à jour des informations de base
    else if (isset($_POST['update_profile'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $fullname = htmlspecialchars(trim($_POST['fullname']), ENT_QUOTES, 'UTF-8');
        $bio = htmlspecialchars(trim($_POST['bio']), ENT_QUOTES, 'UTF-8');
        
        // Requête préparée pour éviter les injections SQL
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, fullname = ?, bio = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $email, $fullname, $bio, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profil mis à jour avec succès";
            
            // Mise à jour des données de session
            $_SESSION['email'] = $email;
            
            // Rafraîchir les données utilisateur avec une requête préparée
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error_message = "Erreur lors de la mise à jour du profil";
            // On ne montre pas l'erreur SQL en production
            error_log("Erreur SQL: " . $conn->error);
        }
    }
    
    // Traitement de l'upload de photo (sécurisé)
    else if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // Vérification du token CSRF (déjà fait plus haut)
        
        $filename = $_FILES['profile_picture']['name'];
        $filesize = $_FILES['profile_picture']['size'];
        $tmp_name = $_FILES['profile_picture']['tmp_name'];
        
        // Vérification de la taille du fichier (max 2MB)
        if ($filesize > 2097152) {
            $error_message = "Le fichier est trop volumineux. Taille maximale: 2MB.";
        } else {
            // Vérification du type MIME
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($tmp_name);
            $allowed_mime_types = [
                'image/jpeg',
                'image/png',
                'image/gif'
            ];
            
            // Vérification de l'extension
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($mime_type, $allowed_mime_types) || !in_array($ext, $allowed_extensions)) {
                $error_message = "Type de fichier non autorisé. Seuls les fichiers JPG, JPEG, PNG et GIF sont acceptés.";
            } else {
                // Génération d'un nom de fichier sécurisé et unique
                $new_filename = bin2hex(random_bytes(16)) . '.' . $ext;
                $upload_path = "uploads/" . $new_filename;
                
                // Déplacement du fichier
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    // Mise à jour de la photo de profil dans la base de données
                    $update_pic_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $update_pic_stmt->bind_param("si", $upload_path, $user_id);
                    
                    if ($update_pic_stmt->execute()) {
                        $success_message = "Photo de profil mise à jour avec succès";
                        
                        // Rafraîchir les données utilisateur
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                    } else {
                        $error_message = "Erreur lors de la mise à jour de la photo";
                        error_log("Erreur SQL: " . $conn->error);
                    }
                } else {
                    $error_message = "Erreur lors de l'upload du fichier";
                }
            }
        }
    }
}

// Vérifier si le dossier uploads existe, sinon le créer
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - TechShop</title>
    <!-- Ajout d'en-têtes de sécurité -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #0066cc;
            --primary-dark: #004080;
            --secondary-color: #e9ecef;
            --accent-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .profile-info {
            margin-top: 20px;
        }
        
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card-title {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .input-file-label {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .input-file-label:hover {
            background-color: var(--primary-dark);
        }
        
        #profile_picture {
            display: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .navbar .nav-link {
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .navbar .nav-link:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-laptop-code me-2"></i>TechShop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-boxes me-1"></i> Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comment.php">
                            <i class="fas fa-comments me-1"></i> Avis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i> Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user-circle me-1"></i> Mon compte
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- En-tête du profil -->
    <div class="profile-header">
        <div class="container text-center">
            <img src="<?php echo isset($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'images/default-profile.jpg'; ?>" alt="Photo de profil" class="profile-picture">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : "Aucune biographie"; ?></p>
                <span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span>
            </div>
        </div>
    </div>

    <!-- Contenu du profil -->
    <div class="container">
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
    
        <div class="row">
            <!-- Informations personnelles -->
            <div class="col-md-8 mb-4">
                <div class="card profile-card">
                    <h4 class="card-title">
                        <i class="fas fa-user me-2"></i>
                        Informations personnelles
                    </h4>
                    <form method="post" action="">
                        <!-- Protection CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié.</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Biographie</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Photo de profil et sécurité -->
            <div class="col-md-4">
                <!-- Upload de photo (sécurisé) -->
                <div class="card profile-card">
                    <h4 class="card-title">
                        <i class="fas fa-camera me-2"></i>
                        Photo de profil
                    </h4>
                    <form method="post" action="" enctype="multipart/form-data">
                        <!-- Protection CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="text-center mb-3">
                            <img src="<?php echo isset($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'images/default-profile.jpg'; ?>" alt="Photo de profil actuelle" class="img-fluid rounded mb-3" style="max-height: 150px;">
                            
                            <label for="profile_picture" class="input-file-label">
                                <i class="fas fa-upload me-1"></i>
                                Choisir une photo
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/gif">
                            <small class="form-text text-muted d-block mt-2">JPG, JPEG, PNG ou GIF. Max 2MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>
                            Mettre à jour la photo
                        </button>
                    </form>
                </div>
                
                <!-- Sécurité -->
                <div class="card profile-card mt-4">
                    <h4 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Sécurité
                    </h4>
                    <a href="change_password.php" class="btn btn-outline-primary mb-3">
                        <i class="fas fa-key me-1"></i>
                        Changer de mot de passe
                    </a>
                    <?php if($user['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-dark">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Accéder au panneau admin
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Historique des commandes (simulé pour l'interface) -->
        <div class="card profile-card">
            <h4 class="card-title">
                <i class="fas fa-shopping-bag me-2"></i>
                Historique des commandes
            </h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Produits</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-2305</td>
                            <td>15/04/2023</td>
                            <td>Laptop Pro X1, Souris Gaming</td>
                            <td>1299.99 €</td>
                            <td><span class="badge bg-success">Livré</span></td>
                        </tr>
                        <tr>
                            <td>#ORD-1896</td>
                            <td>02/03/2023</td>
                            <td>Écouteurs Bluetooth</td>
                            <td>89.99 €</td>
                            <td><span class="badge bg-success">Livré</span></td>
                        </tr>
                        <tr>
                            <td>#ORD-1654</td>
                            <td>10/01/2023</td>
                            <td>Clavier Mécanique, Hub USB</td>
                            <td>159.98 €</td>
                            <td><span class="badge bg-success">Livré</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-laptop-code me-2"></i>TechShop</h5>
                    <p>Votre boutique spécialisée en produits technologiques.</p>
                </div>
                <div class="col-md-3">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Accueil</a></li>
                        <li><a href="products.php" class="text-white">Produits</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><a href="contact.php" class="text-white">Nous contacter</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> TechShop - Version sécurisée</p>
                <p class="small text-muted mt-2">Application corrigée contre les principales vulnérabilités</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher le nom du fichier sélectionné
        document.getElementById('profile_picture').addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Aucun fichier sélectionné';
            document.querySelector('.input-file-label').textContent = fileName;
        });
    </script>
</body>
</html>