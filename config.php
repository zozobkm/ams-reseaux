<?php
// Configuration globale de la base de données
$DB_HOST = "localhost";
$DB_NAME = "box";
$DB_USER = "forumuser";
$DB_PASS = "forum123";

try {
    // On utilise une variable unique $pdo_box pour tout le projet
    $pdo_box = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
    $pdo_box->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur critique de connexion : " . $e->getMessage());
}
?>
