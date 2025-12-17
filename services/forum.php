<?php
session_start();
require_once 'db.php';

/* ===== MODE ADMIN ===== */
$ADMIN_KEY = "admin123";

if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}

/* ===== RÉCUPÉRATION DES MESSAGES ===== */
$sql = "
    SELECT messages.id, messages.contenu, messages.date_post, users.username
    FROM messages
    JOIN users ON messages.user_id = users.id
    ORDER BY messages.date_post DESC
";

$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();
?>

<?php
require_once __DIR__."/../auth/require_login.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>FORUM</title><link rel="stylesheet" href="/ams-reseaux/assets/style.css"></head>
<body>
<?php include __DIR__."/../menu.php"; ?>
<div class="container">


<?php include __DIR__ . '/menu.php'; ?>

<h1>Forum</h1>

<!-- ===== FORMULAIRE MODE ADMIN ===== -->
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

<!-- ===== AFFICHAGE DES MESSAGES ===== -->
<?php if (empty($messages)): ?>
    <p>Aucun message pour le moment.</p>
<?php else: ?>
    <?php foreach ($messages as $msg): ?>
        <div class="message">
            <strong><?= htmlspecialchars($msg['username']) ?></strong>
            <em>(<?= $msg['date_post'] ?>)</em>

            <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>

            <?php if (isset($_SESSION['admin'])): ?>
                <form method="post" action="delete.php">
                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                    <button type="submit">Supprimer</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<hr>

<!-- ===== AJOUT DE MESSAGE ===== -->
<h2>Poster un message</h2>

<form method="post" action="post.php">
    <input type="text" name="username" placeholder="Pseudo" required><br><br>
    <textarea name="contenu" placeholder="Votre message" required></textarea><br><br>
    <button type="submit">Envoyer</button>
</form>

</body>
</html>
