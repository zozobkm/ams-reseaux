<?php
require_once 'db.php';

// Récupération des informations
$username = trim($_POST['username']);
$contenu  = trim($_POST['contenu']);

if ($username === '' || $contenu === '') {
    die("Champs invalides");
}

/* Vérifier l'existence de l'utilisateur */
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    // Si l'utilisateur n'existe pas, le créer
    $stmt = $pdo->prepare("INSERT INTO users(username, password) VALUES(?, '')");
    $stmt->execute([$username]);
    $user_id = $pdo->lastInsertId();
} else {
    // Utilisateur trouvé
    $user_id = $user['id'];
}

/* Insérer le message */
$stmt = $pdo->prepare("INSERT INTO messages(user_id, contenu) VALUES(?, ?)");
$stmt->execute([$user_id, $contenu]);

header("Location: index.php");
exit;
