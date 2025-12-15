<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin'])) {
    die("Accès refusé");
}

if (!isset($_POST['id'])) {
    die("ID manquant");
}

$id = intval($_POST['id']);

$stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
