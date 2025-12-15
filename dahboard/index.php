<?php
require_once __DIR__."/../auth/require_login.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="container">
    <h1>Dashboard Box</h1>

    <div class="card">
        <p><strong>Connecté :</strong> <?= htmlspecialchars($_SESSION["email"]) ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($_SESSION["role"]) ?></p>
        <p><strong>Mode :</strong> <?= htmlspecialchars($_SESSION["mode"]) ?></p>

        <div class="row">
            <a class="btn" href="/ams-reseaux/dashboard/set_mode.php?mode=normal">Mode Normal</a>
            <a class="btn" href="/ams-reseaux/dashboard/set_mode.php?mode=avance">Mode Avancé</a>
        </div>
    </div>

    <div class="grid">
        <a class="tile" href="/ams-reseaux/services/dhcp.php">DHCP</a>
        <a class="tile" href="/ams-reseaux/services/dns.php">DNS</a>
        <a class="tile" href="/ams-reseaux/services/nat.php">NAT</a>
        <a class="tile" href="/ams-reseaux/services/ftp.php">FTP Débit</a>
        <a class="tile" href="/ams-reseaux/services/mail.php">Mail</a>
        <a class="tile" href="/ams-reseaux/services/forum.php">Forum</a>
        <?php if($_SESSION["role"]==="admin"): ?>
            <a class="tile admin" href="/ams-reseaux/admin/users.php">Admin Utilisateurs</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
