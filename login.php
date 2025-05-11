<?php
include 'db.php';
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialiser la variable d'erreur
$error = "";

// Protection contre les attaques par force brute
function checkBruteForce($username) {
    // Implémentation simple limitant les tentatives
    // Dans une version réelle, cela serait stocké en base de données
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    // Réinitialiser le compteur après 30 minutes
    if (time() - $_SESSION['last_attempt_time'] > 1800) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if ($_SESSION['login_attempts'] >= 5) {
        return true; // Blocage actif
    }
    
    return false; // Pas de blocage
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    // Vérifier si le formulaire a un jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Erreur de validation de sécurité. Veuillez réessayer.";
    }
    // Vérifier si l'utilisateur n'est pas bloqué pour cause de trop de tentatives
    elseif (checkBruteForce($_POST['username'])) {
        $error = "Trop de tentatives de connexion. Veuillez réessayer plus tard.";
    }
    else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Protection contre l'injection SQL avec requêtes préparées
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Si le mot de passe est en clair, on profite de la connexion pour le hacher
                if ($password === $user['password']) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $user['id']);
                    $update_stmt->execute();
                }
                
                // Réinitialiser les tentatives de connexion
                $_SESSION['login_attempts'] = 0;
                
                // Régénérer l'ID de session pour éviter la fixation de session
                session_regenerate_id(true);
                
                // Création de la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirection selon le rôle
                if ($user['role'] == 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        }
        
        // Incrémenter le compteur de tentatives échouées
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        
        // Message d'erreur générique pour éviter l'énumération des utilisateurs
        $error = "Identifiants incorrects. Veuillez réessayer.";
    }
}

// Générer un nouveau jeton CSRF pour le formulaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Préparation de l'insertion d'un nouvel utilisateur
$insert_stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name
, role) VALUES (?, ?, ?, ?, 'user')");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TechShop</title>
    
    <!-- En-têtes de sécurité -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net;">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-title {
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
        .login-help {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="login-form">
                    <div class="brand-logo">TechShop</div>
                    <h2 class="login-title">Connexion</h2>
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <!-- Protection CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                    <div class="login-help">
                        <p>Pas encore de compte? <a href="register.php">Inscrivez-vous</a></p>
                        <p><a href="index.php">Retour à l'accueil</a></p>
                        <p class="text-muted small">Indice: admin/admin</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>