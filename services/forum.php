<?php
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/db.php';

$sql="
SELECT messages.id,messages.contenu,messages.date_post,users.username
FROM messages
JOIN users ON messages.user_id=users.id
ORDER BY messages.date_post DESC
";
$stmt=$pdo->query($sql);
$messages=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Forum</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . '/../menu.php'; ?>

<div class="container">
<h1>Forum</h1>

<?php if(empty($messages)): ?>
  <p>Aucun message pour le moment.</p>
<?php else: ?>
  <?php foreach($messages as $msg): ?>
    <div class="message">
      <strong><?= htmlspecialchars($msg["username"]) ?></strong>
      <em>(<?= $msg["date_post"] ?>)</em>
      <p><?= nl2br(htmlspecialchars($msg["contenu"])) ?></p>

      <?php if(($_SESSION["role"]??"user")==="admin"): ?>
        <form method="post" action="delete.php">
          <input type="hidden" name="id" value="<?= (int)$msg["id"] ?>">
          <button type="submit">Supprimer</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<div class="card">
  <h2>Poster un message</h2>
  <form method="post" action="post.php">
    <input type="text" name="username" placeholder="Pseudo" required>
    <textarea name="contenu" placeholder="Votre message" required></textarea>
    <button type="submit">Envoyer</button>
  </form>
</div>

</div>
</body>
</html>
