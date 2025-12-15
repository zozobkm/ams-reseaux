<?php
require_once 'db.php';

if (!isset($_POST['id'])) {
    die("ID manquant");
}

$stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
$stmt->execute([$_POST['id']]);

header("Location: index.php");
exit;
