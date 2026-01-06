<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']); // On utilise l'input "username" pour remplir la colonne "email"
    $contenu = trim($_POST['contenu']);

    if (!empty($email) && !empty($contenu)) {
        try {
            // 1. Vérifier si l'email existe déjà dans box_users
            $stmt = $pdo->prepare("SELECT id FROM box_users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                // 2. Création de l'utilisateur (clé étrangère obligatoire)
                $stmt = $pdo->prepare("INSERT INTO box_users (email, role) VALUES (?, 'user')");
                $stmt->execute([$email]);
                $user_id = $pdo->lastInsertId();
            } else {
                $user_id = $user['id'];
            }

            // 3. Insertion du message dans ta table messages
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, contenu, date_post) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $contenu]);

            header('Location: forum.php');
            exit();
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
}
