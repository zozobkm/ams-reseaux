<?php
require_once __DIR__ . '/../auth/require_login.php';  // Vérification de la connexion

// Vérification du rôle admin
if (($_SESSION["role"] ?? "user") !== "admin") {
    header("Location: /ams-reseaux/services/forum.php");
    exit;
}

require_once __DIR__ . '/db.php';

// Récupération de l'ID du message à supprimer
$id = (int)($_POST["id"] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirection vers la page principale du forum
header("Location: /ams-reseaux/services/forum.php");
exit;
