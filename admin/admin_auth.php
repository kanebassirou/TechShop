<?php
// Protection contre l'accès direct à ce fichier
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accès direct interdit');
}

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    // Utilisateur non connecté, redirection
    header('Location: ../login.php');
    exit;
}

// Vérification stricte du rôle administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Journaliser la tentative d'accès non autorisé
    error_log("Tentative d'accès non autorisé à l'interface admin par l'utilisateur: " . 
        $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ") - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Stocker un message d'erreur dans la session
    $_SESSION['error_message'] = "Accès refusé. Vous devez être administrateur pour accéder à cette section.";
    
    // Rediriger vers la page d'accueil
    header('Location: ../index.php');
    exit;
}
?>