<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier l'adresse IP</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">

</head>
 <?php include('menu.php'); ?>

<body>

<h2>Modification de l'adresse IP (interface eth1)</h2>

<?php
$file = '/etc/network/interfaces';

// Récupérer l'adresse IP actuelle
$current_ip = trim(shell_exec("grep 'address' $file | awk '{print \$2}'"));
if(empty($current_ip)) $current_ip = "192.168.1.1";

// Découper l’adresse en 4 parties
$parts = explode('.', $current_ip);
$prefix = $parts[0].'.'.$parts[1].'.'.$parts[2].'.';
$last = $parts[3];

echo "<p>Adresse IP actuelle : <strong>$current_ip</strong></p>";

if(isset($_POST['new_last'])){
    $new_last = trim($_POST['new_last']);
    $new_ip = $prefix.$new_last;


    // Modifier la ligne dans le fichier interfaces
    $cmd = "sudo sed -i 's/address .*/address $new_ip/' $file";
    shell_exec($cmd);

    // Redémarre l’interface réseau
    shell_exec("sudo ifdown eth1 && sudo ifup eth1");

    // Lire la nouvelle IP réelle
    $new_real_ip = trim(shell_exec("ip -4 addr show eth1 | grep 'inet ' | awk '{print \$2}'"));
    echo "<pre> Nouvelle adresse IP  : $new_ip ($new_real_ip)</pre>";
}
?>

<form method="post">
<label>Adresse réseau (fixée) :</label>
<input type="text" value="<?php echo htmlspecialchars($prefix); ?>" readonly>

<label>Dernier nombre (modifiable) :</label>
<input type="number" name="new_last" value="<?php echo htmlspecialchars($last); ?>" min="1" max="254">

<input type="submit" value="Modifier">
</form>

</body>
</html>
