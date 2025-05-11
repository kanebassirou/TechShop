<?php
session_start();
include 'db.php';

$message_sent = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        // Vulnérable à l'injection de commandes - simulation
        // Dans un cas réel, on utiliserait mail() ou autre fonction
        $command = "echo 'De: $name <$email>\nSujet: $subject\n\n$message' >> messages.txt";
        // exec($command); // Commenté pour éviter l'exécution réelle
        
        $message_sent = true;
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - TechShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .contact-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .contact-form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">TechShop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comment.php">Avis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Mon compte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section de contact -->
    <div class="container my-5">
        <h1 class="text-center mb-5">Contactez-nous</h1>
        
        <div class="row">
            <div class="col-md-5">
                <div class="contact-info">
                    <h3 class="mb-4">Informations de contact</h3>
                    <p><i class="fas fa-map-marker-alt me-3"></i> 123 Rue du Web, 75001 Paris</p>
                    <p><i class="fas fa-phone me-3"></i> +221 77 223 22 22</p>
                    <p><i class="fas fa-envelope me-3"></i> contact@techshop.test</p>
                    <p><i class="fas fa-clock me-3"></i> Lun-Ven: 9h00-18h00</p>
                    
                    <h4 class="mt-5 mb-3">Suivez-nous</h4>
                    <div class="social-icons">
                        <a href="#" class="me-3 text-dark"><i class="fab fa-facebook-square fa-2x"></i></a>
                        <a href="#" class="me-3 text-dark"><i class="fab fa-twitter-square fa-2x"></i></a>
                        <a href="#" class="me-3 text-dark"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="#" class="text-dark"><i class="fab fa-linkedin fa-2x"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="contact-form">
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success">
                            <h4>Message envoyé!</h4>
                            <p>Nous vous répondrons dans les plus brefs délais.</p>
                            <a href="index.php" class="btn btn-primary mt-3">Retour à l'accueil</a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <h3 class="mb-4">Envoyez-nous un message</h3>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Sujet</label>
                                <input type="text" class="form-control" id="subject" name="subject">
                            </div>
                            <div class="mb-4">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Envoyer le message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>TechShop</h5>
                    <p>Votre boutique spécialisée en produits technologiques.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">À propos</a></li>
                        <li><a href="comment.php" class="text-white">Commentaires</a></li>
                        <li><a href="contact.php" class="text-white">Nous contacter</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Administrateur</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-white">Connexion admin</a></li>
                        <li><a href="change_password.php" class="text-white">Changer mot de passe</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> TechShop - Application de démonstration </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>