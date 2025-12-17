<?php
require_once 'db.php';  // Connexion à la base de données

// Récupération des données du formulaire
$username = trim($_POST['username']);
$contenu  = trim($_POST['contenu']);

// Vérification des champs
if ($username === '' || $contenu === '') {
    die("Champs invalides");
}

// Vérifier si l'utilisateur existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    // Si l'utilisateur n'existe pas, on l'ajoute
    $stmt = $pdo->prepare("INSERT INTO users(username, password) VALUES(?, '')");
    $stmt->execute([$username]);
    $user_id = $pdo->lastInsertId();
} else {
    $user_id = $user['id'];
}

// Insertion du message
$stmt = $pdo->prepare("INSERT INTO messages(user_id, contenu) VALUES(?, ?)");
$stmt->execute([$user_id, $contenu]);

// Redirection vers la page du forum
header("Location: index.php");
exit;
