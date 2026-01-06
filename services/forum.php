<?php
// 1. Débug et Session
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// 2. Connexion (Assure-toi que db.php est dans le même dossier)
require_once 'db.php'; 

/* ===== LOGIQUE MODE EXPERT (ADMIN) ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

// Détection du mode pour le badge
$is_admin = isset($_SESSION['admin']);

/* ===== RÉCUPÉRATION DES MESSAGES (Jointure avec box_users.email) ===== */
try {
    $sql = "SELECT messages.id, messages.contenu, messages.date_post, box_users.email 
            FROM messages 
            JOIN box_users ON messages.user_id = box_users.id 
            ORDER BY messages.date_post DESC";
    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_sql = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Forum d'entraide</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Forum d'entraide communautaire</h1>
            <span class="badge" style="background: <?= $is_admin ? '#e67e22' : '#3498db' ?>;">
                <?= $is_admin ? "MODE EXPERT ACTIVÉ" : "MODE NORMAL" ?>
            </span>
        </div>

        <div class="card">
            <?php if (!$is_admin): ?>
                <form method="post" style="display: flex; gap: 10px; align-items: center;">
                    <label style="font-size: 0.9em; color: #64748b;">Accès Modérateur :</label>
                    <input type="password" name="admin_key" placeholder="Clé Secrète" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                    <button type="submit" class="btn-blue" style="background: #1e293b;">Activer</button>
                </form>
            <?php else: ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: #27ae60; margin: 0;"><strong>Session Expert active.</strong> Vous pouvez supprimer les messages inappropriés.</p>
                    <a href="logout.php" class="btn-blue" style="background: #e74c3c; text-decoration: none;">Quitter le mode expert</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Poser une question ou signaler un problème</h3>
            <form method="post" action="post.php" style="margin-top: 15px;">
                <input type="text" name="username" placeholder="Votre Email (ex: alice@illipbox.lan)" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
                <textarea name="contenu" placeholder="Décrivez votre demande ici..." required style="width: 100%; height: 100px; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                <button type="submit" class="btn-blue">Publier sur le forum</button>
            </form>
        </div>

        <div class="card">
            <h3>Discussions récentes</h3>
            
            <?php if (isset($error_sql)): ?>
                <p style="color: #e74c3c;">Erreur SQL : <?= htmlspecialchars($error_sql) ?></p>
            <?php elseif (empty($messages)): ?>
                <p style="color: #94a3b8; font-style: italic;">Aucune discussion en cours.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div style="border-left: 4px solid #3498db; padding: 15px; margin-bottom: 20px; background: #f8fafc; border-radius: 0 5px 5px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <strong style="color: #1e293b;"><?= htmlspecialchars($msg['email']) ?></strong>
                            <small style="color: #94a3b8;"><?= $msg['date_post'] ?></small>
                        </div>
                        <p style="margin: 0; color: #334155; line-height: 1.5;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                        
                        <?php if ($is_admin): ?>
                            <form method="post" action="delete.php" onsubmit="return confirm('Supprimer ce message ?');" style="margin-top: 10px;">
                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 0.8em; padding: 0; font-weight: bold;">
                                    [🗑️ Supprimer]
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
