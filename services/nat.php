<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";
$resultat = "";

if (isset($_POST['activer_nat'])) {
    // Exécution du script S5 [cite: 127]
    $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec($cmd . " 2>&1");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration NAT - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . "/../menu.php"; ?>
<div class="main-content">
    <div class="header-status">
        <h1>Partage de connexion (NAT)</h1>
        <span class="badge-mode"><?= htmlspecialchars($_SESSION["mode"]) ?></span>
    </div>

    <div class="card">
        <h3>🛡️ État du Pare-feu</h3>
        [cite_start]<p>Le NAT permet aux appareils de votre réseau local d'accéder à Internet[cite: 214].</p>
        <form method="post">
            <button type="submit" name="activer_nat" class="btn">Activer le NAT / Masquerade</button>
        </form>
    </div>

    <?php if ($resultat !== ""): ?>
        <div class="card" style="margin-top:20px; background:#1e293b; color:#38bdf8; font-family:monospace;">
            <h4>Logs système :</h4>
            <pre><?= htmlspecialchars($resultat) ?></pre>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
