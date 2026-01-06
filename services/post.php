<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
    $email = $_POST['username']; // On récupère l'email saisi
    $contenu = $_POST['contenu'];

    try {
        // 1. On cherche l'ID de l'utilisateur via son EMAIL
        $stmt = $pdo->prepare("SELECT id FROM box_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // 2. On insère le message avec le bon user_id
            $insert = $pdo->prepare("INSERT INTO messages (user_id, contenu) VALUES (?, ?)");
            $insert->execute([$user['id'], $contenu]);
            header("Location: forum.php?success=1");
        } else {
            die("Erreur : L'utilisateur avec l'email '$email' n'existe pas dans la base.");
        }
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>
