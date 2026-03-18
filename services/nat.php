<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// 1. Restriction d'accès : Uniquement pour le mode Expert
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

if (!$is_avance) {
    header("Location: /ams-reseaux/dahboard/index.php");
    exit;
}

$resultat = "";

// 2. Logique d'activation du NAT Global (Masquerade)
if (isset($_POST['activer_nat'])) {
    // Active le forwarding et le partage de connexion
    $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec($cmd . " 2>&1");
}

// 3. Logique d'ajout d'une redirection de port (PAT)
if (isset($_POST['add_pat'])) {
    $port_ext = intval($_POST['port_ext']);
    $ip_dest = $_POST['ip_dest'];
    $port_dest = intval($_POST['port_dest']);
    
    //  Redirection de l'adresse (Table NAT)
    $cmd_nat = "sudo iptables -t nat -A PREROUTING -p tcp --dport $port_ext -j DNAT --to-destination $ip_dest:$port_dest";
    shell_exec($cmd_nat);
    
    //  Ouverture du pare-feu (
    // On utilise -I pour placer la règle avant le DROP par défaut
    $cmd_forward = "sudo iptables -I FORWARD -p tcp -d $ip_dest --dport $port_dest -j ACCEPT";
    shell_exec($cmd_forward);
    
    $resultat = "Règle PAT activée : Le trafic sur le port $port_ext est redirigé vers $ip_dest:$port_dest";
}

// 4. Récupération des règles actuelles pour affichage 
$rules_list = shell_exec("sudo iptables -t nat -L PREROUTING -n --line-numbers");
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
            <span class="badge" style="background: #e67e22;">Mode Expert Actif</span>
        </div>

        <div class="card">
            <h3>Partage de connexion (NAT)</h3>
            <p style="color: #555;">Permet aux appareils locaux d'accéder à Internet en utilisant l'adresse IP WAN de la Box.</p>
            <form method="post" style="margin-top: 15px;">
                <button type="submit" name="activer_nat" class="btn-blue">
                    Activer le NAT / Masquerade
                </button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Redirection de ports (PAT)</h3>
            <p style="color: #555;">Rendre un service interne (serveur web, jeu, etc.) accessible depuis l'extérieur du réseau.</p>
            
            <form method="post" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label>Port Externe (WAN)</label>
                    <input type="number" name="port_ext" placeholder="ex: 8080" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div>
                    <label>IP Destination (Client)</label>
                    <input type="text" name="ip_dest" placeholder="192.168.10.11" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div>
                    <label>Port Destination</label>
                    <input type="number" name="port_dest" placeholder="ex: 8888" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <button type="submit" name="add_pat" class="btn-blue">Appliquer la règle</button>
            </form>
        </div>

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none; margin-top:20px;">
                <h4 style="color: #94a3b8; margin-top: 0;">Logs système :</h4>
                <pre style="font-family: 'Courier New', monospace; white-space: pre-wrap; margin-bottom: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 20px; background: #f8fafc;">
            <h4>Règles de redirection actives (iptables) :</h4>
            <pre style="font-size: 0.85em; color: #334155;"><?= htmlspecialchars($rules_list) ?></pre>
        </div>

    </div>
</body>
</html>
