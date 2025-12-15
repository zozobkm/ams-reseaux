<?php
require_once 'db.php';

$sql = "
    SELECT messages.contenu, messages.date_post, users.username
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
</head>
<body>

<h1>Forum</h1>

<?php if(empty($messages)): ?>
    <p>Aucun message pour le moment.</p>
<?php else: ?>
    <?php foreach($messages as $msg): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <strong><?= htmlspecialchars($msg['username']) ?></strong>
            <em>(<?= $msg['date_post'] ?>)</em>
            <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
