<?php
$host = 'localhost';
$dbname = 'box';       // Nom de la base de données
$user = 'forumuser';   // Utilisateur de la base de données
$pass = 'forum123';    // Mot de passe pour l'utilisateur

try {
    // Connexion PDO à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());  // Afficher l'erreur si la connexion échoue
}
?>
