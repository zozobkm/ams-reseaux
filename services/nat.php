<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Vérification du mode Expert
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

if (!$is_avance) {
    // Redirection si l'utilisateur n'est pas expert
    header("Location: /ams-reseaux/dahboard/index.php");
    exit;
}

$resultat = "";

// 1. Activation du NAT Global
if (isset($_POST['activer_nat'])) {
    $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec($cmd . " 2>&1");
}

// 2. Ajout d'une règle PAT
if (isset($_POST['add_pat'])) {
    $port_ext = intval($_POST['port_ext']);
    $ip_dest = $_POST['ip_dest'];
    $port_dest = intval($_POST['port_dest']);
    
    // Commande iptables DNAT
    $cmd_pat = "sudo iptables -t nat -A PREROUTING -p tcp --dport $port_ext -j DNAT --to-destination $ip_dest:$port_dest";
    shell_exec($cmd_pat);
    $resultat = "Règle ajoutée : Port externe $port_ext redirigé vers $ip_dest:$port_dest";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Sécurité et NAT</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Sécurité et NAT</h1>
            <span class="badge" style="background: #e67e22;">
                Mode Expert Actif
            </span>
        </div>

        <div class="card">
            <h3>Partage de connexion (NAT)</h3>
            <p style="color: #555;">Activez le Masquerade pour permettre aux clients du réseau local d'accéder à Internet.</p>
            <form method="post" style="margin-top: 15px;">
                <button type="submit" name="activer_nat" class="btn-blue">
                    Activer le NAT / Masquerade
                </button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Redirection de ports (PAT)</h3>
            <p style="color: #555;">Autorisez l'accès à un service interne depuis l'extérieur du réseau.</p>
            
            <form method="post" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label>Port Externe</label>
                    <input type="number" name="port_ext" placeholder="ex: 8080" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div>
                    <label>IP Destination</label>
                    <input type="text" name="ip_dest" placeholder="192.168.10.11" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div>
                    <label>Port Destination</label>
                    <input type="number" name="port_dest" placeholder="ex: 80" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <button type="submit" name="add_pat" class="btn-blue">Ajouter la règle</button>
            </form>
        </div>

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none; margin-top:20px;">
                <h4 style="color: #94a3b8; margin-top: 0;">Logs système :</h4>
                <pre style="font-family: 'Courier New', monospace; white-space: pre-wrap; margin-bottom: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
