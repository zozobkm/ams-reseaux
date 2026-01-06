<?php
session_start();
require_once 'db.php'; // Connexion via le fichier du dossier services 

/* ===== MODE ADMIN ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES ===== */
$sql = "SELECT messages.id, messages.contenu, messages.date_post, users.username 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.date_post DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Forum d'entraide</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php include __DIR__ . '/../templates/menu.php'; ?>

    <div class="main-content">
        
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Espace d'échange communautaire</h1>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="mode-badge" style="background: #e67e22; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8em;">Mode Expert Activé</span>
            <?php endif; ?>
        </div>

        <div class="forum-card" style="background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px;">
            <h3>Paramètres du forum</h3>
            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="post">
                    <input type="password" name="admin_key" placeholder="Clé admin" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <button type="submit" class="btn-post" style="background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Activer mode admin</button>
                </form>
            <?php else: ?>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <p style="margin: 0; color: #27ae60;"><strong>Mode administrateur actif</strong></p>
                    <form method="post" action="logout_admin.php" style="margin: 0;">
                        <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Quitter</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="forum-card" style="background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px;">
            <h3>Poster un message</h3>
            <form method="post" action="post.php">
                <input type="text" name="username" placeholder="Pseudo" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <textarea name="contenu" placeholder="Décrivez votre problème technique..." required style="width: 100%; height: 100px; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                <button type="submit" class="btn-post" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Publier sur le forum</button>
            </form>
        </div>

        <div class="forum-card" style="background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px;">
            <h3>Discussions récentes</h3>
            
            <?php if (empty($messages)): ?>
                <p>Aucun message pour le moment.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message" style="border-left: 4px solid #3498db; padding-left: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <div class="message-meta" style="font-size: 0.85em; color: #7f8c8d; margin-bottom: 5px;">
                            Posté par <span style="font-weight: bold; color: #2c3e50;"><?= htmlspecialchars($msg['username']) ?></span> 
                            le <?= $msg['date_post'] ?>
                        </div>
                        <p style="margin: 5px 0;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                        
                        <?php if (isset($_SESSION['admin'])): ?>
                            <form method="post" action="delete.php" style="margin-top: 5px;">
                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 0.8em; padding: 0;">[Supprimer ce message]</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
