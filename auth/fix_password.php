<?php
require_once "../services/db.php";

// On définit le mot de passe simple
$nouveau_password = "123";
// On génère le hash proprement via PHP
$hash = password_hash($nouveau_password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("UPDATE box_users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, 'user@box.local']);
    echo "✅ Succès ! Le mot de passe de user@box.local est maintenant : 123";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
