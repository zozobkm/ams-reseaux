<?php
require_once 'db.php'; // Connexion locale 

$username = trim($_POST['username'] ?? '');
$contenu  = trim($_POST['contenu'] ?? '');

if ($username === '' || $contenu === '') {
    die("Erreur : Tous les champs sont obligatoires.");
}

try {
    /* 1. Vérifier si l'utilisateur existe déjà */
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        /* 2. S'il n'existe pas, on le crée (mot de passe vide par défaut) */
        $stmt = $pdo->prepare("INSERT INTO users(username, password) VALUES(?, '')");
        $stmt->execute([$username]);
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['id'];
    }

    /* 3. Insérer le message lié à cet utilisateur */
    $stmt = $pdo->prepare("INSERT INTO messages(user_id, contenu, date_post) VALUES(?, ?, NOW())");
    $stmt->execute([$user_id, $contenu]);

    /* 4. Retour au forum */
    header("Location: forum.php");
    exit;

} catch (Exception $e) {
    die("Erreur base de données : " . $e->getMessage());
}
