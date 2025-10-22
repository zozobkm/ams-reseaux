<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier l'adresse IP</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:20px;text-align:center}
h2{color:#333}
form{background:white;padding:20px;margin:20px auto;border-radius:10px;width:420px;box-shadow:0 0 5px #ccc;text-align:left}
label{display:block;margin-top:10px}
input[type=text],input[type=number]{width:100%;padding:5px}
input[readonly]{background:#eee;color:#555}
input[type=submit]{margin-top:15px;background:#0078d7;color:white;border:none;padding:8px 15px;border-radius:5px;cursor:pointer}
input[type=submit]:hover{background:#005fa3}
pre{background:#eee;padding:10px;text-align:left;border-radius:5px}
</style>
</head>
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

    echo "<pre>⏳ Modification en cours...</pre>";

    // Modifier la ligne dans le fichier interfaces
    $cmd = "sudo sed -i 's/address .*/address $new_ip/' $file";
    shell_exec($cmd);

    // Redémarre l’interface réseau
    shell_exec("sudo ifdown eth1 && sudo ifup eth1");

    // Lire la nouvelle IP réelle
    $new_real_ip = trim(shell_exec("ip -4 addr show eth1 | grep 'inet ' | awk '{print \$2}'"));
    echo "<pre>✅ Nouvelle adresse IP appliquée : $new_ip ($new_real_ip)</pre>";
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
