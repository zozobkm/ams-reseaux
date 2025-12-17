<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";
require_once __DIR__ . "/../config/db.php";

// Vérification de la connexion à la base de données
if (!$pdo) {
    die("Erreur de connexion à la base de données.");
}

// Récupération des messages
$sql = "
    SELECT messages.id, messages.contenu, messages.date_post, users.username
    FROM messages
    JOIN users ON messages.user_id = users.id
    ORDER BY messages.date_post DESC
";

$stmt = $pdo->query($sql);

// Si la requête échoue
if ($stmt === false) {
    die("Erreur lors de la récupération des messages.");
}

$messages = $stmt->fetchAll();

// Débogage : Afficher les messages récupérés
// echo '<pre>';
// var_dump($messages);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php include __DIR__ . "/../menu.php"; ?>

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
