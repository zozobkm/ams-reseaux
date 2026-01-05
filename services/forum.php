<?php
session_start();
require_once 'db.php'; // Connexion via le fichier du dossier services 

/* ===== MODE ADMIN ===== */
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES (Correction de la jointure) ===== */
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
    <title>Forum - Box Internet</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css"> </head>
<body>
<?php include __DIR__ . '/../templates/menu.php'; ?> <div class="container">
    <h1>Forum de discussion</h1>

    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="post">
            <input type="password" name="admin_key" placeholder="Clé admin">
            <button type="submit">Activer mode admin</button>
        </form>
    <?php else: ?>
        <p><strong>Mode administrateur activé</strong></p>
        <form method="post" action="logout_admin.php">
            <button type="submit">Quitter le mode admin</button>
        </form>
    <?php endif; ?>

    <hr>

    <?php if (empty($messages)): ?>
        <p>Aucun message pour le moment.</p>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message" style="background:white; padding:10px; margin-bottom:10px; border-radius:5px; border: 1px solid #ccc;">
                <strong><?= htmlspecialchars($msg['username']) ?></strong> 
                <small>(<?= $msg['date_post'] ?>)</small>
                <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                <?php if (isset($_SESSION['admin'])): ?>
                    <form method="post" action="delete.php">
                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                        <button type="submit" style="color:red;">Supprimer</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <h2>Poster un message</h2>
    <form method="post" action="post.php">
        <input type="text" name="username" placeholder="Pseudo" required><br><br>
        <textarea name="contenu" placeholder="Votre message" required style="width:100%; height:100px;"></textarea><br><br>
        <button type="submit">Envoyer le message</button>
    </form>
</div>
</body>
</html>
