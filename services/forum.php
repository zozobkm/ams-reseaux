<?php
// 1. Affichage des erreurs pour le débug pendant tes tests
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. Connexion à la base de données (Assure-toi que db.php est dans le même dossier)
require_once 'db.php'; 

/* ===== LOGIQUE MODE EXPERT (ADMIN) ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES (Jointure avec box_users.email) ===== */
try {
    // On récupère les messages en joignant la table box_users pour avoir l'email
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
    <style>
        /* Styles de secours pour garantir le design Dashboard si le CSS charge mal */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 0; display: flex; }
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .mode-badge { background: #e67e22; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .btn-post { background: #3498db; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .message-item { border-left: 5px solid #3498db; padding: 15px; margin-bottom: 20px; background: #fafafa; border-radius: 0 5px 5px 0; }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
    </style>
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header">
            <h1>Forum d'entraide communautaire</h1>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="mode-badge">MODE EXPERT ACTIVÉ</span>
            <?php endif; ?>
        </div>

        <div class="card">
            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="post" style="display: flex; gap: 10px; align-items: center;">
                    <label>Accès Administrateur :</label>
                    <input type="password" name="admin_key" placeholder="Clé Secrète" style="width: 200px; margin-bottom: 0;">
                    <button type="submit" class="btn-post" style="background: #2c3e50;">Activer</button>
                </form>
            <?php else: ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: #27ae60; margin: 0;"><strong>Bienvenue, Administrateur.</strong> Vous pouvez modérer les messages.</p>
                    <a href="logout_admin.php" style="color: #e74c3c; text-decoration: none; font-weight: bold; border: 1px solid #e74c3c; padding: 5px 15px; border-radius: 5px;">Quitter le mode expert</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Poser une question ou signaler un problème</h3>
            <form method="post" action="post.php">
                <input type="text" name="username" placeholder="Votre Email ou Pseudo" required>
                <textarea name="contenu" placeholder="Décrivez votre demande ici..." required></textarea>
                <button type="submit" class="btn-post">Publier sur le forum</button>
            </form>
        </div>

        <div class="card">
            <h3>Discussions récentes</h3>
            
            <?php if (isset($error_sql)): ?>
                <p style="color: red;">Erreur de base de données : <?= $error_sql ?></p>
            <?php elseif (empty($messages)): ?>
                <p>Aucun message n'a été posté pour le moment.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold; color: #2c3e50;"><?= htmlspecialchars($msg['email']) ?></span>
                            <span style="font-size: 0.85em; color: #95a5a6;"><?= $msg['date_post'] ?></span>
                        </div>
                        <p style="margin: 10px 0; color: #34495e; line-height: 1.6;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                        
                        <?php if (isset($_SESSION['admin'])): ?>
                            <form method="post" action="delete.php" onsubmit="return confirm('Supprimer définitivement ce message ?');">
                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #e74c3c; cursor: pointer; padding: 0; font-size: 0.85em;">[🗑️ Supprimer ce message]</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
