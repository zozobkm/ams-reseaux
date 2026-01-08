<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

$resultat = "";
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- Logique d'exécution des scripts ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['auto'])) {
        $nb = filter_var($_POST['nb'], FILTER_VALIDATE_INT);
        if ($nb !== false && $nb >= 1 && $nb <= 250) {
            $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh $nb";
            $resultat = shell_exec($cmd . " 2>&1");
        } else {
            $resultat = "Erreur : Le nombre d'appareils doit être un entier entre 1 et 250.";
        }
    } 
    elseif (isset($_POST['manuel']) && $is_avance) {
        // Validation des octets (uniquement le dernier chiffre de l'IP)
        $start_octet = filter_var($_POST['debut_octet'], FILTER_VALIDATE_INT);
        $end_octet = filter_var($_POST['fin_octet'], FILTER_VALIDATE_INT);

        if ($start_octet !== false && $end_octet !== false && 
            $start_octet >= 2 && $end_octet <= 254 && 
            $start_octet < $end_octet) {
            
            // Reconstruction de l'IP complète pour le script
            $debut_ip = escapeshellarg("192.168.1." . $start_octet);
            $fin_ip = escapeshellarg("192.168.1." . $end_octet);

            $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dhcp_manuel.sh $debut_ip $fin_ip";
            $resultat = shell_exec($cmd . " 2>&1");
        } else {
            $resultat = "Erreur : Plage invalide. Utilisez des nombres entre 2 et 254 (le début doit être inférieur à la fin).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Configuration DHCP</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .ip-input-group {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            max-width: 300px;
        }
        .ip-prefix {
            padding: 10px;
            background: #e2e8f0;
            color: #475569;
            font-weight: bold;
            border-right: 1px solid #cbd5e1;
        }
        .ip-octet {
            flex: 1;
            border: none;
            padding: 10px;
            outline: none;
            font-size: 16px;
        }
    </style>
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
            <p>Le service DHCP attribue automatiquement une adresse IP à chaque appareil. Le préfixe réseau est fixé sur <strong>192.168.1.0/24</strong>.</p>
        </div>

        <div class="card">
            <?php if (!$is_avance): ?>
                <h3>Configuration simplifiée</h3>
                <form method="post">
                    <label>Nombre d'appareils (1-250) :</label><br>
                    <input type="number" name="nb" min="1" max="250" value="10" required 
                           style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100px; margin: 10px 0;">
                    <br>
                    <button type="submit" name="auto" class="btn-blue">Appliquer la configuration</button>
                </form>
            <?php else: ?>
                <h3>Configuration Avancée</h3>
                <form method="post">
                    <div style="margin-bottom: 15px;">
                        <label>Début de plage :</label>
                        <div class="ip-input-group">
                            <span class="ip-prefix">192.168.1.</span>
                            <input type="number" name="debut_octet" class="ip-octet" min="2" max="253" value="10" required>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Fin de plage :</label>
                        <div class="ip-input-group">
                            <span class="ip-prefix">192.168.1.</span>
                            <input type="number" name="fin_octet" class="ip-octet" min="3" max="254" value="50" required>
                        </div>
                    </div>
                    <button type="submit" name="manuel" class="btn-blue" style="background: #e67e22;">
                        Mettre à jour le serveur DHCP
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none;">
                <h4 style="color: #94a3b8; margin-top: 0;">📜 Logs système :</h4>
                <pre style="white-space: pre-wrap; margin: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
