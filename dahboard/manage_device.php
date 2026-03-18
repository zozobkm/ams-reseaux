<?php
session_start();
require_once __DIR__ . '/../config.php';

$mac = $_GET['mac'] ?? null;
if (!$mac) { header("Location: index.php"); exit; }

// On récupère les infos de l'appareil
$stmt = $pdo_box->prepare("SELECT * FROM devices WHERE mac_address = ?");
$stmt->execute([$mac]);
$device = $stmt->fetch();

// Si on change le statut
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update = $pdo_box->prepare("UPDATE devices SET statut_debit = ? WHERE mac_address = ?");
    $update->execute([$new_status, $mac]);
    
    // Ici, on pourrait ajouter l'exécution d'un script Python pour brider le débit réel !
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer l'appareil - <?= htmlspecialchars($mac) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <h1>Configuration de l'appareil</h1>
        <div class="dashboard-card">
            <p><strong>IP :</strong> <?= htmlspecialchars($device['ip_address']) ?></p>
            <p><strong>MAC :</strong> <?= htmlspecialchars($device['mac_address']) ?></p>
            
            <form method="post" style="margin-top: 20px;">
                <label>Statut du débit :</label>
                <select name="status" style="padding: 8px; margin: 0 10px;">
                    <option value="normal" <?= $device['statut_debit'] == 'normal' ? 'selected' : '' ?>>Normal (Illimité)</option>
                    <option value="limite" <?= $device['statut_debit'] == 'limite' ? 'selected' : '' ?>>Limité (1 Mbps)</option>
                    <option value="alerte" <?= $device['statut_debit'] == 'alerte' ? 'selected' : '' ?>>Alerte (Bloqué)</option>
                </select>
                <button type="submit" name="update_status" class="btn-blue">Enregistrer</button>
                <a href="index.php" style="margin-left: 10px; color: #6b7280;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>
