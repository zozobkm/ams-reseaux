<?php
session_start();
require_once 'db.php'; 

/* ===== RÉCUPÉRATION DES MESSAGES ===== */
try {
    // On récupère le contenu de ta table messages
    // en le liant à la table box_users pour avoir le nom
    $sql = "SELECT messages.id, messages.contenu, messages.date_post, box_users.username 
            FROM messages 
            JOIN box_users ON messages.user_id = box_users.id 
            ORDER BY messages.date_post DESC";
    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si l'erreur "Unknown column username" revient, changez le nom de la colonne ci-dessous
    die("Erreur technique : La colonne 'username' n'existe pas dans la table 'box_users'. Vérifiez son nom avec 'DESC box_users;'.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Forum</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content" style="margin-left: 260px; padding: 30px;">
        <div class="header">
            <h1>Espace d'échange communautaire</h1>
            <span class="mode-badge" style="background:#e67e22; color:white; padding:5px 15px; border-radius:20px;">Mode Expert</span>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3>Poser une question</h3>
            <form method="post" action="post.php">
                <input type="text" name="username" placeholder="Votre Pseudo" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd;">
                <textarea name="contenu" placeholder="Description de votre problème..." required style="width:100%; height:80px; padding:10px; margin-bottom:10px; border:1px solid #ddd;"></textarea>
                <button type="submit" style="background:#3498db; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Envoyer</button>
            </form>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3>Discussions récentes</h3>
            <?php foreach ($messages as $msg): ?>
                <div class="message" style="border-left: 4px solid #3498db; padding-left:15px; margin-bottom:20px; border-bottom: 1px solid #eee; padding-bottom:10px;">
                    <div style="font-size: 0.9em; color:#7f8c8d;">
                        <strong><?= htmlspecialchars($msg['username']) ?></strong> <small>(<?= $msg['date_post'] ?>)</small>
                    </div>
                    <p style="margin-top:5px;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
