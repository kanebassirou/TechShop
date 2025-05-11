<?php
include 'db.php';
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    
    // Vérifier si le nom d'utilisateur existe déjà (vulnérable à l'injection SQL)
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $error = "Ce nom d'utilisateur est déjà utilisé.";
    } else {
        // Insérer le nouvel utilisateur (vulnérable à l'injection SQL)
        $sql = "INSERT INTO users (username, password, email, full_name) 
                VALUES ('$username', '$password', '$email', '$full_name')";
        
        if ($conn->query($sql) === TRUE) {
            $success = true;
        } else {
            $error = "Erreur lors de l'inscription : " . $conn->error;
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
                    
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
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
</body>
</html>