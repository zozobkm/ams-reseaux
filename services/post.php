<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['contenu'])) {
    $username = trim($_POST['username']);
    $contenu = trim($_POST['contenu']);

    if (!empty($username) && !empty($contenu)) {
        try {
            // 1. Chercher si l'utilisateur existe déjà dans box_users
            $stmt = $pdo->prepare("SELECT id FROM box_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                $user_id = $user['id'];
            } else {
                // 2. Créer l'utilisateur s'il n'existe pas pour éviter l'erreur de clé étrangère
                $stmt = $pdo->prepare("INSERT INTO box_users (username) VALUES (?)");
                $stmt->execute([$username]);
                $user_id = $pdo->lastInsertId();
            }

            // 3. Insérer le message lié à l'ID trouvé ou créé
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, contenu, date_post) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $contenu]);

            header('Location: forum.php');
            exit();
        } catch (PDOException $e) {
            die("Erreur base de données : " . $e->getMessage());
        }
    }
}
header('Location: forum.php');
