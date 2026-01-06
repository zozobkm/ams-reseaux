<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $contenu = $_POST['contenu'];

    try {
        // 1. Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM box_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            // Créer l'utilisateur s'il n'existe pas pour respecter la clé étrangère
            $stmt = $pdo->prepare("INSERT INTO box_users (username) VALUES (?)");
            $stmt->execute([$username]);
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        // 2. Insérer le message dans ta table messages
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, contenu, date_post) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $contenu]);

        header('Location: forum.php');
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
