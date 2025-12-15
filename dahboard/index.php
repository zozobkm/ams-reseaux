<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Box</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php include __DIR__ . '/../menu.php'; ?>

<h1>Tableau de bord</h1>

<p>Connecté en tant que : <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong></p>
<p>Rôle : <strong><?= htmlspecialchars($_SESSION["role"]) ?></strong></p>
<p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

<hr>

<form method="post" action="toggle_mode.php">
    <button type="submit">
        Passer en mode <?= $_SESSION["mode"] === "normal" ? "avancé" : "normal" ?>
    </button>
</form>

<hr>

<div class="grid">
    <a class="card" href="/ams-reseaux/services/forum.php">Forum</a>
    <a class="card" href="/ams-reseaux/services/dhcp.php">DHCP</a>
    <a class="card" href="/ams-reseaux/services/dns.php">DNS</a>
    <a class="card" href="/ams-reseaux/services/nat.php">NAT</a>
    <a class="card" href="/ams-reseaux/services/ftp.php">Débit FTP</a>
    <a class="card" href="/ams-reseaux/services/mail.php">Mail</a>
</div>

</body>
</html>
