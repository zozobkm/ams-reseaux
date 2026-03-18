<?php
session_start();

// 1. Vérification de connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config.php';

$mac = $_GET['mac'] ?? null;
if (!$mac) { 
    header("Location: index.php"); 
    exit; 
}

// 3. Récupération des infos de l'appareil
try {
    $stmt = $pdo_box->prepare("SELECT * FROM devices WHERE mac_address = ?");
    $stmt->execute([$mac]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

// 4. Gestion de la mise à jour du statut
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    try {
        $update = $pdo_box->prepare("UPDATE devices SET statut_debit = ? WHERE mac_address = ?");
        $update->execute([$new_status, $mac]);
        
    
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CeriBox - Gérer l'appareil</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php 
$menu_path = __DIR__ . '/../menu.php';
if (file_exists($menu_path)) {
    include $menu_path;
}
?>

<div class="main-content">
    <div class="header-page">
        <div>
            <h1><i class="fas fa-sliders-h"></i> Configuration de l'appareil</h1>
            <p style="color: var(--text-muted);">Gestion des limites réseau pour l'adresse MAC : <strong><?= htmlspecialchars($mac) ?></strong></p>
        </div>
        <a href="index.php" class="btn-blue" style="background: #64748b; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid" style="grid-template-columns: 1fr;">
        <div class="dashboard-card">
            <div style="display: flex; gap: 40px; margin-bottom: 30px; border-bottom: 1px solid #f3f4f6; padding-bottom: 20px;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Adresse IP</span>
                    <p style="font-size: 1.2rem; font-weight: 600; margin-top: 5px;"><?= htmlspecialchars($device['ip_address']) ?></p>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Adresse MAC</span>
                    <p style="font-size: 1.2rem; font-weight: 600; margin-top: 5px; font-family: monospace;"><?= htmlspecialchars($device['mac_address']) ?></p>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Dernière détection</span>
                    <p style="font-size: 1.1rem; margin-top: 5px;"><?= $device['last_seen'] ?></p>
                </div>
            </div>

            <form method="post">
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 500;">Sélectionner le profil de débit :</label>
                    <select name="status" style="width: 100%; max-width: 400px; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb; font-size: 1rem;">
                        <option value="normal" <?= $device['statut_debit'] == 'normal' ? 'selected' : '' ?>>Normal (Accès complet)</option>
                        <option value="limite" <?= $device['statut_debit'] == 'limite' ? 'selected' : '' ?>>Limité (Bridage à 1 Mbps)</option>
                        <option value="alerte" <?= $device['statut_debit'] == 'alerte' ? 'selected' : '' ?>>Alerte (Connexion coupée)</option>
                    </select>
                </div>

                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 25px;">
                    <p style="font-size: 0.9rem; color: #475569;">
                        <i class="fas fa-info-circle"></i> 
                        En changeant le statut, la Box appliquera automatiquement les règles de filtrage via <strong>iptables</strong> et <strong>tc</strong> pour cette adresse MAC.
                    </p>
                </div>

                <button type="submit" name="update_status" class="btn-blue" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
