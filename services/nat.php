<?php
session_start();
require_once __DIR__."/../auth/require_login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NAT</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="container">
    <h1>NAT Configuration</h1>
    <p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

    <?php if ($_SESSION["mode"] === "avance"): ?>
        <!-- Zone avancée -->
        <div class="card">
            <h3>Configuration avancée du NAT</h3>
            <form method="post" action="apply_nat_advanced.php">
                <!-- Configuration plus poussée pour le mode avancé -->
                <label for="port_forwarding">Redirection de port:</label>
                <input type="text" name="port_forwarding" id="port_forwarding" placeholder="Port à rediriger" required>
                <button type="submit">Appliquer la configuration avancée</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Zone normal -->
        <div class="card">
            <h3>Configuration simple du NAT</h3>
            <form method="post" action="apply_nat_simple.php">
                <label for="device_count">Nombre d'appareils :</label>
                <input type="number" name="device_count" id="device_count" min="1" required>
                <button type="submit">Appliquer</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
