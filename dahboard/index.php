<?php
session_start();

// 1. Vérification de connexion
if (!isset($_SESSION["user_id"])) {
    
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config.php';

// 3. Logique du bouton "Scanner le réseau"
if (isset($_POST['run_scan'])) {
    // Exécution du script Python
    shell_exec('sudo /usr/bin/python3 /var/www/html/ams-reseaux/scripts/device_scanner.py');
    
    header("Location: /ams-reseaux/dahboard/index.php");
    exit;
}

// 4. Récupération des données
$query_devices = "SELECT * FROM devices ORDER BY last_seen DESC";
$result_devices = mysqli_query($conn, $query_devices);

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php 
$menu_path = __DIR__ . '/../menu.php';
if (file_exists($menu_path)) { include $menu_path; }
?>

<div class="main-content">
    <div class="header-page">
        <div>
            <h1>Tableau de bord</h1>
            <p style="color: var(--text-muted);">Bienvenue, <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong></p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" class="btn-blue" style="background: <?= $is_avance ? '#f59e0b' : '#2563eb' ?>;">
                <i class="fas <?= $is_avance ? 'fa-unlock' : 'fa-lock' ?>"></i> 
                Mode <?= $is_avance ? "Normal" : "Avancé" ?>
            </button>
        </form>
    </div>

    <div class="dashboard-grid">
        <a href="/ams-reseaux/services/dhcp.php" class="dashboard-card">
            <i class="fas fa-network-wired card-icon" style="color: #2563eb;"></i>
            <h3>Service DHCP</h3>
            <p>Gestion des IP locales.</p>
        </a>
        <a href="/ams-reseaux/services/nat.php" class="dashboard-card">
            <i class="fas fa-shield-virus card-icon" style="color: #ef4444;"></i>
            <h3>Sécurité & NAT</h3>
            <p>Pare-feu et ports.</p>
        </a>
    </div>

    <div class="dashboard-card" style="margin-top: 30px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3><i class="fas fa-microchip"></i> Appareils connectés</h3>
            <form method="post">
                <button type="submit" name="run_scan" class="btn-blue">
                    <i class="fas fa-sync-alt"></i> Scanner le réseau
                </button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb; color: var(--text-muted); font-size: 0.85rem;">
                        <th style="padding: 12px;">ADRESSE IP</th>
                        <th style="padding: 12px;">ADRESSE MAC</th>
                        <th style="padding: 12px;">DERNIÈRE ACTIVITÉ</th>
                        <th style="padding: 12px;">DÉBIT</th>
                        <th style="padding: 12px; text-align: center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_devices && mysqli_num_rows($result_devices) > 0): ?>
                        <?php while($dev = mysqli_fetch_assoc($result_devices)): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($dev['ip_address']) ?></td>
                            <td style="padding: 12px;"><code><?= htmlspecialchars($dev['mac_address']) ?></code></td>
                            <td style="padding: 12px; font-size: 0.85rem; color: #6b7280;"><?= $dev['last_seen'] ?></td>
                            <td style="padding: 12px;">
                                <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; background: #d1fae5; color: #065f46;">
                                    <?= strtoupper(htmlspecialchars($dev['statut_debit'])) ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <button class="btn-blue" style="padding: 6px 10px; background: #64748b;" title="Gérer">
                                    <i class="fas fa-sliders"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">
                                <i class="fas fa-search"></i> Aucun appareil trouvé. Cliquez sur "Scanner".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
