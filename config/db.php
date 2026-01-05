<?php
/**
 * Fichier de liaison pour la base de données
 * Ce fichier appelle la configuration centrale à la racine
 */

// On remonte d'un dossier pour trouver le fichier config.php principal
require_once __DIR__ . "/../config.php";

/**
 * Pour assurer la compatibilité avec tes anciens fichiers :
 * On crée l'alias $pdo qui pointe vers $pdo_box (défini dans config.php)
 */
if (isset($pdo_box)) {
    $pdo = $pdo_box;
} else {
    // Sécurité au cas où config.php n'est pas trouvé
    die("Erreur : La configuration principale (config.php) est introuvable.");
}
?>
