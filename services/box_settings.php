<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once "db.php";

$file = '/etc/network/interfaces';
$current_ip = trim(shell_exec("grep 'address' $file | awk '{print $2}'")) ?: "192.168.10.1";

$parts = explode('.', $current_ip);
$prefix = $parts[0].'.'.$parts[1].'.'.$parts[2].'.';
$last = $parts[3] ?? "1";

$msg = "";
if(isset($_POST['new_last'])){
    $new_ip = $prefix . trim($_POST['new_last']);
    // Commande sed pour modifier l'IP système
    shell_exec("sudo sed -i 's/address .*/address $new_ip/' $file");
    shell_exec("sudo ifdown eth1 && sudo ifup eth1");
    $msg = "L'adresse IP de la Box a été mise à jour : $new_ip";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres Système - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . '/../menu.php'; ?>

<div class="main-content">
    <div class="header-status">
        <h1>Paramètres de la Box</h1>
        <span class="badge-mode">Expert</span>
    </div>

    <div class="card">
        <h3>🌐 Configuration de l'interface LAN (eth1)</h3>
        <p>Adresse actuelle : <strong><?= htmlspecialchars($current_ip) ?></strong></p>
        
        <form method="post" style="margin-top: 20px;">
            <label>Préfixe réseau :</label>
            <input type="text" value="<?= $prefix ?>" readonly style="background:#eee; width: 150px;">
            
            <label>Dernier octet :</label>
            <input type="number" name="new_last" value="<?= $last ?>" min="1" max="254" style="width: 80px;">
            
            <button type="submit" class="btn">Mettre à jour l'IP</button>
        </form>
    </div>

    <?php if($msg): ?>
        <div class="card" style="border-left: 5px solid #10b981;">
            <p>✅ <?= $msg ?></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
