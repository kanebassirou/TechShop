<?php
include 'db.php';
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Génération du token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Erreur de validation du formulaire. Veuillez réessayer.";
    } else {
        // Validation et nettoyage des entrées
        $username = trim(filter_var($_POST['username'], FILTER_SANITIZE_SPECIAL_CHARS));
        $password = $_POST['password'];
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $fullname = trim(filter_var($_POST['full_name'], FILTER_SANITIZE_SPECIAL_CHARS));
        
        // Validation supplémentaire
        if (empty($username) || empty($password) || empty($email) || empty($fullname)) {
            $error = "Tous les champs sont obligatoires";
        } 
        elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = "Le nom d'utilisateur doit comporter entre 3 et 50 caractères";
        }
        elseif (strlen($password) < 8) {
            $error = "Le mot de passe doit comporter au moins 8 caractères";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email n'est pas valide";
        }
        else {
            // Vérifier si le nom d'utilisateur existe déjà (sécurisé contre l'injection SQL)
            $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result && $check_result->num_rows > 0) {
                $error = "Ce nom d'utilisateur est déjà utilisé.";
            } else {
                // Vérifier si l'email existe déjà
                $check_email_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $check_email_stmt->bind_param("s", $email);
                $check_email_stmt->execute();
                $check_email_result = $check_email_stmt->get_result();
                
                if ($check_email_result && $check_email_result->num_rows > 0) {
                    $error = "Cette adresse email est déjà utilisée.";
                } else {
                    // Hachage sécurisé du mot de passe avant stockage
                    // Utilisation de l'algorithme PASSWORD_DEFAULT (actuellement bcrypt)
                    // Dans un environnement de production, considérer l'utilisation de PASSWORD_ARGON2ID
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT, [
                        'cost' => 12 // Augmenter le coût pour une sécurité accrue, nécessite plus de ressources
                    ]);
                    
                    // Insérer le nouvel utilisateur (sécurisé contre l'injection SQL)
                    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'user')");
                    $insert_stmt->bind_param("ssss", $username, $hashed_password, $email, $fullname);
                    
                    try {
                        if ($insert_stmt->execute()) {
                            $success = true;
                            // Régénérer le token CSRF après une inscription réussie
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        } else {
                            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                            error_log("Erreur d'insertion : " . $insert_stmt->error);
                        }
                    } catch (Exception $e) {
                        $error = "Une erreur est survenue lors de l'inscription.";
                        error_log("Exception lors de l'inscription : " . $e->getMessage());
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TechShop</title>
    
    <!-- Ajout d'en-têtes de sécurité -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net;">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
            padding-bottom: 50px;
        }
        .register-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .register-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 20px;
            color: #0066cc;
            font-size: 24px;
            font-weight: bold;
        }
        .password-requirements {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-form">
                    <div class="brand-logo">TechShop</div>
                    <h2 class="register-title">Créer un compte</h2>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            Compte créé avec succès! <a href="login.php">Connectez-vous</a>
                        </div>
                    <?php else: ?>
                    
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" novalidate>
                            <!-- Protection CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       minlength="3" maxlength="50"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <div class="password-requirements">
                                    Le mot de passe doit contenir au moins 8 caractères
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">S'inscrire</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Déjà inscrit? <a href="login.php">Connectez-vous</a></p>
                            <p><a href="index.php">Retour à l'accueil</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>