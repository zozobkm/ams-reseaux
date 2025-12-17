
<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";
require_once __DIR__ . "/../config/db.php";

// Récupération des messages
$sql = "
    SELECT messages.id, messages.contenu, messages.date_post, users.username
    FROM messages
    JOIN users ON messages.user_id = users.id
    ORDER BY messages.date_post DESC
";

$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();

// Si l'on soumet un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
    $username = trim($_POST['username']);
    $contenu  = trim($_POST['contenu']);

    // On vérifie que les champs sont remplis
    if ($username !== '' && $contenu !== '') {
        // Vérification si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            // Si l'utilisateur n'existe pas, on l'ajoute
            $stmt = $pdo->prepare("INSERT INTO users(username,password) VALUES(?, '')");
            $stmt->execute([$username]);
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        // On insère le message
        $stmt = $pdo->prepare("INSERT INTO messages(user_id, contenu) VALUES(?, ?)");
        $stmt->execute([$user_id, $contenu]);
    }
}

// Mode admin
$ADMIN_KEY = "admin123";
if (isset($_POST['admin_key']) && $_POST['admin_key'] === $ADMIN_KEY) {
    $_SESSION['admin'] = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>FORUM</title><link rel="stylesheet" href="/ams-reseaux/assets/style.css"></head>
<body>
<?php include __DIR__."/../menu.php"; ?>
<div class="container">


<h1>Forum</h1>

<!-- Formulaire mode admin -->
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

<!-- Affichage des messages -->
<?php if (empty($messages)): ?>
    <p>Aucun message pour le moment.</p>
<?php else: ?>
    <?php foreach ($messages as $msg): ?>
        <div class="message">
            <strong><?= htmlspecialchars($msg['username']) ?></strong>
            <em>(<?= $msg['date_post'] ?>)</em>
            <p><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>

            <!-- Formulaire pour supprimer les messages (seul admin peut voir ce formulaire) -->
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

<!-- Formulaire pour poster un message -->
<h2>Poster un message</h2>
<form method="post" action="forum.php">
    <input type="text" name="username" placeholder="Pseudo" required><br><br>
    <textarea name="contenu" placeholder="Votre message" required></textarea><br><br>
    <button type="submit">Envoyer</button>
</form>

</body>
</html>
