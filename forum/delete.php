<?php
require_once 'db.php';

$id = intval($_POST['id']);

$stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
