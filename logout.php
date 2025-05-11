<?php

// Démarrer la session pour accéder aux variables de session
session_start();

// Vérification CSRF pour une déconnexion sécurisée
// Uniquement si la déconnexion est déclenchée par POST (meilleure sécurité)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Journalisation de la tentative
        error_log("Tentative de déconnexion sans token CSRF valide");
        
        // Redirection avec un message d'erreur
        header("Location: index.php?error=security_violation");
        exit;
    }
}

// Destruction de toutes les variables de session
$_SESSION = array();

// Suppression du cookie de session avec des paramètres de sécurité renforcés
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        [
            'expires' => time() - 42000,
            'path' => $params["path"],
            'domain' => $params["domain"],
            'secure' => true,  // Force HTTPS
            'httponly' => true, // Empêche l'accès JavaScript
            'samesite' => 'Lax' // Protection CSRF
        ]
    );
}

// Destruction de la session
session_destroy();

// En-têtes pour empêcher la mise en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

// Redirection vers la page d'accueil
header("Location: index.php?logout=success");
exit;
?>