<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Configuration DHCP</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:20px;text-align:center}
h2{color:#333}
form{background:white;padding:20px;margin:20px auto;border-radius:10px;width:400px;box-shadow:0 0 5px #ccc;text-align:left}
label{display:block;margin-top:10px}
input[type=text]{width:100%;padding:5px}
input[type=submit]{margin-top:15px;background:#0078d7;color:white;border:none;padding:8px 15px;border-radius:5px;cursor:pointer}
input[type=submit]:hover{background:#005fa3}
</style>
</head>
<body>

<h2>Configuration DHCP automatique</h2>

<?php
// Récupère l'adresse IP de l'interface interne (eth1 ou enp0s8)
$ip = trim(shell_exec("ip -4 addr show eth1 | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
if(empty($ip)){
    $ip = trim(shell_exec("ip -4 addr show enp0s8 | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
}

// Calcule le réseau (dernier octet à 0)
$parts = explode('.', $ip);
$reseau = "$parts[0].$parts[1].$parts[2].0";

// Calcule plage auto
$debut = "$parts[0].$parts[1].$parts[2].10";
$fin   = "$parts[0].$parts[1].$parts[2].50";
$passerelle = $ip;
?>

<form method="post">
<label>Adresse réseau :</label>
<input type="text" name="reseau" value="<?php echo $reseau; ?>" readonly>
<label>Masque :</label>
<input type="text" name="masque" value="255.255.255.0" readonly>
<label>Début plage :</label>
<input type="text" name="debut" value="<?php echo $debut; ?>">
<label>Fin plage :</label>
<input type="text" name="fin" value="<?php echo $fin; ?>">
<label>Passerelle :</label>
<input type="text" name="passerelle" value="<?php echo $passerelle; ?>" readonly>
<input type="submit" name="appliquer" value="Appliquer">
</form>

<?php
if(isset($_POST['appliquer'])){
 $r=$_POST['reseau'];
 $m=$_POST['masque'];
 $d=$_POST['debut'];
 $f=$_POST['fin'];
 $p=$_POST['passerelle'];
 echo "<pre>";
 echo shell_exec("sudo bash ../scripts/config_dhcp_auto.sh $r $m $d $f $p 2>&1");
 echo "</pre>";
}
?>
</body>
</html>
