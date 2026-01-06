<?php
session_start();
require_once 'db.php';

// Sécurité : on vérifie que l'utilisateur est bien admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die("Accès refusé : vous devez être administrateur.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // Suppression du message dans ta table messages
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: forum.php');
        exit();
    } catch (PDOException $e) {
        die("Erreur de suppression : " . $e->getMessage());
    }
}
