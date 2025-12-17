<?php
$host = 'localhost';
$dbname = 'box';  // Assurez-vous que la base de données est correcte
$user = 'forumuser';
$pass = 'forum123';

try {
    // Crée une nouvelle connexion PDO à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Active la gestion des erreurs
} catch (PDOException $e) {
    // Si une erreur se produit, elle sera affichée
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
