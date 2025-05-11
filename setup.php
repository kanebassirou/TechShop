<?php
// Création du dossier images s'il n'existe pas
if (!file_exists('images')) {
    mkdir('images', 0777, true);
}

// Message de confirmation
echo "Structure de dossiers créée avec succès!";
echo "<br><a href='index.php'>Retour à l'accueil</a>";
?>