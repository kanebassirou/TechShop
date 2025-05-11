<?php
/**
 * Connexion sécurisée à la base de données 
 * Version corrigée - Utilisation de mysqli avec préparation des requêtes
 */

// Définition des constantes de configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vulnapp');

// Gestion des erreurs
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Création de la connexion
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Définition du jeu de caractères
    $conn->set_charset("utf8mb4");
    
    // Vérification de la connexion de façon sécurisée
    if ($conn->connect_error) {
        // Journal de l'erreur sans exposer les détails sensibles
        error_log("Erreur de connexion à la base de données: " . $conn->connect_error);
        
        // Message d'erreur générique pour l'utilisateur
        die("Impossible de se connecter à la base de données. Veuillez contacter l'administrateur.");
    }
} catch (Exception $e) {
    // Journalisation de l'erreur
    error_log("Exception lors de la connexion à la base de données: " . $e->getMessage());
    
    // Message d'erreur générique
    die("Une erreur s'est produite lors de l'accès à l'application.");
}
?>