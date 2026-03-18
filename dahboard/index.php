<?php
session_start();

// 1. Vérification de connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

// 2. Inclusion de la configuration base de données
require_once __DIR__ . '/../config.php';

// 3. Gestion des modes (Tâche S6)
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// 4. Logique du bouton "Scanner le réseau"
if (isset($_POST['run_scan'])) {
    // Exécution du script Python de traitement de caractères
    shell_exec('sudo python3 /var/www/html/ams-reseaux/scripts/device_scanner.py');
    // Rafraîchissement pour afficher les nouvelles données
    header("Location: /ams-reseaux/dashboard/index.php");
    exit;
}

// 5. Récupération des appareils depuis la table SQL
$query_devices = "SELECT * FROM devices ORDER BY last_seen DESC";
$result_devices = mysqli_query($conn, $query_devices);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CeriBox - Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php 
// Inclusion du menu latéral
$menu_path = __DIR__ . '/../menu.php';
if (file_exists($menu_path)) {
    include $menu_path;
}
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
        <a href="/ams-reseaux/services/forum.php" class="dashboard-card">
            <i class="fas fa-comments card-icon" style="color: #10b981;"></i>
            <h3>Forum Communautaire</h3>
            <p>Accédez aux discussions et entraidez les clients du FAI.</p>
        </a>

        <a href="/ams-reseaux/services/dhcp.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-network-wired card-icon"></i>
            <h3>Service DHCP</h3>
            <p>Gestion de l'attribution dynamique des adresses IP locales.</p>
        </a>

        <a href="/ams-reseaux/services/dns.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-database card-icon"></i>
            <h3>Service DNS</h3>
            <p>Résolution de noms et annuaire local du domaine box.local.</p>
        </a>

        <a href="/ams-reseaux/services/mail.php" class="dashboard-card">
            <i class="fas fa-envelope-open-text card-icon" style="color: #6366f1;"></i>
            <h3>Messagerie Postfix</h3>
            <p>Consultez et envoyez vos emails via le serveur local.</p>
        </a>

        <a href="/ams-reseaux/services/ftp.php" class="dashboard-card">
            <i class="fas fa-gauge-high card-icon" style="color: #ec4899;"></i>
            <h3>Débit & Performance</h3>
            <p>Tests de bande passante et transferts de fichiers FTP.</p>
        </a>

        <a href="/ams-reseaux/services/nat.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-shield-virus card-icon"></i>
            <h3>Sécurité & NAT</h3>
            <p>Configuration du pare-feu et redirection de ports (PAT).</p>
        </a>
    </div>

    <div class="dashboard-card" style="margin-top: 30px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3><i class="fas fa-microchip"></i> Appareils détectés sur le réseau</h3>
            <form method="post">
                <button type="submit" name="run_scan" class="btn-blue" style="font-size: 0.8rem;">
                    <i class="fas fa-sync-alt"></i> Scanner le réseau
                </button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb; color: var(--text-muted); font-size: 0.9rem;">
                        <th style="padding: 12px;">ADRESSE IP</th>
                        <th style="padding: 12px;">ADRESSE MAC</th>
                        <th style="padding: 12px;">DERNIÈRE ACTIVITÉ</th>
                        <th style="padding: 12px;">DÉBIT</th>
                        <th style="padding: 12px; text-align: center;">GESTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_devices) > 0): ?>
                        <?php while($dev = mysqli_fetch_assoc($result_devices)): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($dev['ip_address']) ?></td>
                            <td style="padding: 12px;"><code><?= htmlspecialchars($dev['mac_address']) ?></code></td>
                            <td style="padding: 12px; font-size: 0.85rem; color: #6b7280;"><?= $dev['last_seen'] ?></td>
                            <td style="padding: 12px;">
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;
                                    background: <?= $dev['statut_debit'] == 'normal' ? '#d1fae5' : '#fee2e2' ?>; 
                                    color: <?= $dev['statut_debit'] == 'normal' ? '#065f46' : '#991b1b' ?>;">
                                    <?= strtoupper(htmlspecialchars($dev['statut_debit'])) ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <button class="btn-blue" style="padding: 5px 10px; background: #6b7280;" title="Configurer les limites">
                                    <i class="fas fa-sliders"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                <i class="fas fa-search"></i> Aucun appareil n'a encore été enregistré.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($is_avance): ?>
    <div class="dashboard-card" style="margin-top: 30px; border: 1px dashed #f59e0b; background: #fffbeb;">
        <h3 style="color: #b45309;"><i class="fas fa-triangle-exclamation"></i> Administration Système</h3>
        <p>Le mode avancé est activé. Vous avez un accès direct aux réglages de la Box Ubuntu.</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
