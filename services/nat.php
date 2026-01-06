<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

$resultat = "";
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

if (isset($_POST['activer_nat'])) {
    // Exécution du script de configuration NAT
    $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec($cmd . " 2>&1");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Configuration NAT</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Partage de connexion (NAT)</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($_SESSION["mode"])) ?>
            </span>
        </div>

        <div class="card">
            <h3>🛡️ Sécurité & Pare-feu</h3>
            <p style="color: #555; line-height: 1.6;">
                Le NAT (Network Address Translation) permet à tous les appareils de votre réseau local 
                de partager la connexion internet de la CeriBox en masquant leurs adresses IP privées.
            </p>
            
            <div style="margin-top: 25px; padding: 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <p><strong>Action requise :</strong> Cliquez sur le bouton ci-dessous pour appliquer les règles <em>iptables</em> et activer le Masquerade.</p>
                
                <form method="post" style="margin-top: 15px;">
                    <button type="submit" name="activer_nat" class="btn-blue">
                        Activer le NAT / Masquerade
                    </button>
                </form>
            </div>
        </div>

        

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none;">
                <h4 style="color: #94a3b8; margin-top: 0;">📜 Logs d'exécution du script :</h4>
                <pre style="font-family: 'Courier New', monospace; white-space: pre-wrap; margin-bottom: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>

        <?php if ($is_avance): ?>
            <div class="card" style="border-left: 5px solid #e67e22;">
                <h3 style="color: #e67e22;">Informations Expert</h3>
                <p style="font-size: 0.9em;">
                    Le script utilise la commande <code>iptables -t nat -A POSTROUTING</code>. 
                    Assurez-vous que l'IP Forwarding est activé dans le noyau Linux (<code>sysctl net.ipv4.ip_forward=1</code>).
                </p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
