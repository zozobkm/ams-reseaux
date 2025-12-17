<?php
session_start();
require_once __DIR__."/../auth/require_login.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DNS</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="container">
    <h1>DNS Configuration</h1>
    <p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

    <?php if ($_SESSION["mode"] === "avance"): ?>
        <!-- Zone avancée -->
        <div class="card">
            <h3>Configuration avancée DNS</h3>
            <form method="post" action="apply_dns_advanced.php">
                <!-- Champs pour gestion des enregistrements DNS -->
                <label for="dns_record">Enregistrement A (ex : www.example.com):</label>
                <input type="text" name="dns_record" id="dns_record" placeholder="Nom d'hôte" required>
                <label for="ip_address">Adresse IP:</label>
                <input type="text" name="ip_address" id="ip_address" placeholder="Adresse IP" required>
                <button type="submit">Appliquer la configuration avancée</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Zone normal -->
        <div class="card">
            <h3>Configuration DNS simplifiée</h3>
            <form method="post" action="apply_dns_simple.php">
                <!-- Configuration DNS basique -->
                <label for="dns_server">Serveur DNS principal :</label>
                <input type="text" name="dns_server" id="dns_server" placeholder="DNS primaire" required>
                <button type="submit">Appliquer</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
