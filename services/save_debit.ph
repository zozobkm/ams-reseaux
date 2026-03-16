<?php
require_once 'db.php';
session_start();
if (isset($_POST['debit']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO historique_debit (user_id, debit, date_mesure) VALUES (?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $_POST['debit']]);
}
?>
