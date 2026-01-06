<?php
session_start();
require_once 'db.php'; 

/* ===== MODE ADMIN ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES (Correction : box_users) ===== */
$sql = "SELECT messages.id, messages.contenu, messages.date_post, box_users.username 
        FROM messages 
        JOIN box_users ON messages.user_id = box_users.id 
        ORDER BY messages.date_post DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Forum</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/menu.php'; ?>

    <div class="main-content">
        <div class="header" style="display: flex; justify-content: space-between; padding: 20px;">
            <h1>Forum de discussion</h1>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="mode-badge" style="background:#e67e22; color:white; padding:5px 15px; border-radius:20px;">Mode Expert Activé</span>
            <?php endif; ?>
        </div>

        <div class="forum-card" style="background:white; padding:20px; margin:20px; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="post">
                    <input type="password" name="admin_key" placeholder="Clé admin">
                    <button type="submit">Activer mode admin</button>
                </form>
            <?php else: ?>
                <p>Mode administrateur actif. <a href="logout_admin.php" style="color:red;">Déconnexion</a></p>
            <?php endif; ?>
        </div>

        <div class="forum-card" style="background:white; padding:20px; margin:20px; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>Poster un message</h3>
            <form method="post" action="post.php">
                <input type="text" name="username" placeholder="Votre pseudo" required style="width:100%; padding:10px; margin-bottom:10px;">
                <textarea name="contenu" placeholder="Votre message" required style="width:100%; height:80px;"></textarea>
                <button type="submit" style="background:#3498db; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Envoyer</button>
            </form>
        </div>

        <div class="forum-card" style="background:white; padding:20px; margin:20px; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>Discussions</h3>
            <?php foreach ($messages as $msg): ?>
                <div class="message" style="border-left: 4px solid #3498db; padding-left:15px; margin-bottom:20px;">
                    <strong><?= htmlspecialchars($msg['username']) ?></strong> 
                    <small style="color:gray;">(<?= $msg['date_post'] ?>)</small>
                    <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                    <?php if (isset($_SESSION['admin'])): ?>
                        <form method="post" action="delete.php">
                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                            <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">[Supprimer]</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
