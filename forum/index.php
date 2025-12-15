<?php
session_start();
require_once 'db.php';

$sql = "
    SELECT messages.id, messages.contenu, messages.date_post, users.username
    FROM messages
    JOIN users ON messages.user_id = users.id
    ORDER BY messages.date_post DESC
";

$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php include __DIR__ . '/menu.php'; ?>

<h1>Forum</h1>

<?php if(empty($messages)): ?>
    <p>Aucun message pour le moment.</p>
<?php else: ?>
    <?php foreach($messages as $msg): ?>
        <div class="message">
            <strong><?= htmlspecialchars($msg['username']) ?></strong>
            <em>(<?= $msg['date_post'] ?>)</em>

            <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>

            <?php if($msg['username'] === 'admin'): ?>
                <form method="post" action="delete.php">
                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                    <button type="submit">Supprimer</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Poster un message</h2>

<form method="post" action="post.php">
    <input type="text" name="username" placeholder="Pseudo" required><br><br>
    <textarea name="contenu" placeholder="Votre message" required></textarea><br><br>
    <button type="submit">Envoyer</button>
</form>

</body>
</html>
