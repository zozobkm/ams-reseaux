<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Changer d'adresse IP</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:20px;text-align:center}
h2{color:#333}
form{background:white;padding:20px;margin:20px auto;border-radius:10px;width:420px;box-shadow:0 0 5px #ccc;text-align:left}
label{display:block;margin-top:10px}
input[type=text]{width:100%;padding:5px}
input[type=submit]{margin-top:15px;background:#0078d7;color:white;border:none;padding:8px 15px;border-radius:5px;cursor:pointer}
input[type=submit]:hover{background:#005fa3}
pre{background:#eee;padding:10px;text-align:left;overflow:auto;border-radius:5px}
</style>
</head>
<body>

<h2>Changement d'adresse IP (mode automatique)</h2>
<form method="post">
<label>Nombre d'appareils :</label>
<input type="text" name="nb_appareils" placeholder="Ex : 5">
<input type="submit" name="auto" value="Appliquer">
</form>

<?php
if(isset($_POST['auto'])){
 $n=$_POST['nb_appareils'];
 echo "<h3>Mode automatique : $n appareils</h3>";

 // Détecter interface
 $interface = trim(shell_exec("ip -o link show | awk -F': ' '{print \$2}' | grep -E '^eth1|enp0s8' | head -n1"));
 $current_ip = trim(shell_exec("ip -4 addr show $interface | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
 if(empty($current_ip)) $current_ip = "192.168.1.1";

 // Calcul automatique
 $parts = explode('.', $current_ip);
 $new_ip = "$parts[0].$parts[1].$parts[2].16";
 $mask = "255.255.255.0";

 echo "<pre>";
 echo ">> Attribution auto de l'addr $new_ip/24 à $interface\n";
 echo shell_exec("sudo ifconfig $interface $new_ip netmask $mask");
 echo ">> Nouvelle IP appliquée : $new_ip/24 avec gateway $current_ip\n";
 echo "</pre>";
}
?>

<h2>Configuration manuelle (mode avancé)</h2>
<form method="post">
<label>Adresse réseau :</label>
<input type="text" name="reseau" value="192.168.1.0">
<label>Masque :</label>
<input type="text" name="masque" value="255.255.255.0">
<label>Début plage :</label>
<input type="text" name="debut" value="192.168.1.10">
<label>Fin plage :</label>
<input type="text" name="fin" value="192.168.1.20">
<label>Passerelle :</label>
<input type="text" name="passerelle" value="192.168.1.1">
<input type="submit" name="manuel" value="Appliquer">
</form>

<?php
if(isset($_POST['manuel'])){
 $r=$_POST['reseau'];
 $m=$_POST['masque'];
 $d=$_POST['debut'];
 $f=$_POST['fin'];
 $p=$_POST['passerelle'];

 echo "<pre>";
 echo "Configuration manuelle du réseau : $r\n";
 echo "Plage d'adresses : $d - $f\n";
 echo "Passerelle : $p\n";
 echo shell_exec("sudo bash ../scripts/config_dhcp_auto.sh $r $m $d $f $p 2>&1");
 echo "</pre>";
}
?>
</body>
</html>
