<?php
// 1. ACTIVER L'AFFICHAGE DES ERREURS (Pour ne plus avoir de page blanche)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. VERIFIER LA CONNEXION (Assure-toi que db.php est bien dans le même dossier)
require_once 'db.php'; 

/* ===== MODE ADMIN ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES (Correction : box_users) ===== */
try {
    $sql = "SELECT messages.id, messages.contenu, messages.date_post, box_users.username 
            FROM messages 
            JOIN box_users ON messages.user_id = box_users.id 
            ORDER BY messages.date_post DESC";
    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    die("Erreur SQL : " . $e->getMessage());
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
    <?php 
    if (file_exists(__DIR__ . '/../menu.php')) {
        include __DIR__ . '/../menu.php'; 
    } else {
        echo "<p style='color:red;'>Erreur : menu.php non trouvé à la racine.</p>";
    }
    ?>

    <div class="main-content" style="margin-left: 260px; padding: 20px;">
        <h1>Forum d'entraide</h1>

        <div class="card" style="background:white; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #ddd;">
            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="post">
                    <input type="password" name="admin_key" placeholder="Clé admin">
                    <button type="submit">Mode Expert</button>
                </form>
            <?php else: ?>
                <span style="color:orange;">● Mode Expert Activé</span>
            <?php endif; ?>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; border:1px solid #ddd; margin-bottom:20px;">
            <h3>Nouveau message</h3>
            <form method="post" action="post.php">
                <input type="text" name="username" placeholder="Pseudo" required style="width:100%; margin-bottom:10px; padding:8px;">
                <textarea name="contenu" placeholder="Votre message..." required style="width:100%; height:80px; padding:8px;"></textarea>
                <button type="submit" style="background:#3498db; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Envoyer</button>
            </form>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; border:1px solid #ddd;">
            <?php if (empty($messages)): ?>
                <p>Aucun message.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div style="border-left: 4px solid #3498db; padding-left: 15px; margin-bottom:15px;">
                        <strong><?= htmlspecialchars($msg['username']) ?></strong> <small>(<?= $msg['date_post'] ?>)</small>
                        <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
