<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once "db.php";

// Détection du mode pour le design
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

$file = '/etc/network/interfaces';
$current_ip = trim(shell_exec("grep 'address' $file | awk '{print $2}'")) ?: "192.168.10.1";

$parts = explode('.', $current_ip);
$prefix = $parts[0].'.'.$parts[1].'.'.$parts[2].'.';
$last = $parts[3] ?? "1";

$msg = "";
if(isset($_POST['new_last'])){
    $new_ip = $prefix . trim($_POST['new_last']);
    
    // Commande sed pour modifier l'IP système (nécessite les droits sudo)
    shell_exec("sudo sed -i 's/address .*/address $new_ip/' $file");
    shell_exec("sudo ifdown eth1 && sudo ifup eth1");
    
    $msg = "L'adresse IP de la Box a été mise à jour : $new_ip";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Paramètres Système</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Paramètres de la Box</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($mode)) ?>
            </span>
        </div>

        <div class="card">
            <h3>🌐 Configuration de l'interface LAN (eth1)</h3>
            <p style="color: #555; margin-bottom: 20px;">
                L'interface **eth1** gère la communication avec vos appareils locaux. 
                Modifier son adresse IP impactera la passerelle par défaut de tous vos clients.
            </p>
            
            <p>Adresse actuelle : <strong style="color: #2c3e50;"><?= htmlspecialchars($current_ip) ?></strong></p>
            
            <form method="post" style="margin-top: 25px; max-width: 500px;">
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Préfixe réseau :</label>
                        <input type="text" value="<?= $prefix ?>" readonly 
                               style="background:#f1f5f9; width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 5px; color: #64748b;">
                    </div>
                    
                    <div style="width: 120px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Dernier octet :</label>
                        <input type="number" name="new_last" value="<?= $last ?>" min="1" max="254" required 
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn-blue" style="width: 100%;">
                        Mettre à jour l'adresse IP
                    </button>
                </div>
            </form>
        </div>

        

        <?php if($msg): ?>
            <div class="card" style="border-left: 5px solid #27ae60; background: #f0fdf4;">
                <p style="color: #166534; font-weight: bold; margin: 0;">✅ <?= htmlspecialchars($msg) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_avance): ?>
            <div class="card" style="border-left: 5px solid #e67e22;">
                <h3 style="color: #e67e22;">Configuration Bas-Niveau</h3>
                <p style="font-size: 0.9em; line-height: 1.5;">
                    Cette action modifie directement le fichier <code>/etc/network/interfaces</code>. 
                    Une mauvaise configuration peut entraîner une perte de connectivité avec la Box.
                </p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
