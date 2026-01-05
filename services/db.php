<?php
// On utilise le chemin relatif pour atteindre config.php à la racine
require_once __DIR__ . "/../config.php";

/** * On crée l'alias $pdo pour ne pas avoir à modifier 
 * tes autres fichiers (forum.php, post.php, etc.)
 */
$pdo = $pdo_box; 
?>
