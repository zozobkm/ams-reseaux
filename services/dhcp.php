<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

$resultat = "";
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- Logique d'exécution des scripts ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['auto'])) {
        $nb = intval($_POST['nb']);
        // On n'envoie que le nombre d'appareils
        $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh $nb";
        $resultat = shell_exec($cmd . " 2>&1");
    } 
    elseif (isset($_POST['manuel']) && $is_avance) {
        // On récupère les IPs et on les nettoie
        $debut = escapeshellarg($_POST['debut']);
        $fin = escapeshellarg($_POST['fin']);
        // IMPORTANT : On n'envoie plus le mot "manuel" ici
        $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dhcp_manuel.sh $debut $fin";
        $resultat = shell_exec($cmd . " 2>&1");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Configuration DHCP</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Service DHCP (isc-dhcp-server)</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($mode)) ?>
            </span>
        </div>

        <div class="card">
            <h3>📡 Attribution des adresses IP</h3>
            <p style="color: #555; line-height: 1.6;">
                Le service DHCP permet à la CeriBox d'attribuer automatiquement une adresse IP à chaque appareil 
                connecté à votre réseau. Sans lui, vous devriez configurer chaque PC à la main.
            </p>
        </div>

        

        <div class="card">
            <?php if (!$is_avance): ?>
                <h3>Configuration simplifiée</h3>
                <p>Indiquez simplement le nombre d'appareils que vous prévoyez de connecter.</p>
                <form method="post" style="margin-top: 20px;">
                    <label style="display: block; margin-bottom: 8px;">Nombre d'appareils :</label>
                    <input type="number" name="nb" min="1" max="250" value="10" required 
                           style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100px; margin-bottom: 15px;">
                    <br>
                    <button type="submit" name="auto" class="btn-blue">Appliquer la configuration</button>
                </form>
            <?php else: ?>
                <h3>Configuration Avancée</h3>
                <p>Définissez précisément la plage d'adresses IP pour votre réseau local.</p>
                <form method="post" style="margin-top: 20px; max-width: 400px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px;">Début de plage :</label>
                        <input type="text" name="debut" value="192.168.1.10" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px;">Fin de plage :</label>
                        <input type="text" name="fin" value="192.168.1.50" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <button type="submit" name="manuel" class="btn-blue" style="background: #e67e22;">
                        Mettre à jour le serveur DHCP
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none;">
                <h4 style="color: #94a3b8; margin-top: 0;">📜 Logs système (DHCPD) :</h4>
                <pre style="font-family: 'Courier New', monospace; white-space: pre-wrap; margin: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
