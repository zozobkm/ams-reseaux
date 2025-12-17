<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['admin'])) {
    header("Location: /ams-reseaux/services/forum.php");
    exit;
}

require_once __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // Supprimer le message avec l'ID donné
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id=?");
    $stmt->execute([$id]);
}

// Rediriger vers le forum après la suppression
header("Location: /ams-reseaux/services/forum.php");
exit;
