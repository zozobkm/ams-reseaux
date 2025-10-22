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
 $n=intval($_POST['nb_appareils']);
 if($n<1)$n=1;

 // Détecte l’interface réseau
 $interface = trim(shell_exec("ip -o link show | awk -F': ' '{print \$2}' | grep -E '^eth1|enp0s8' | head -n1"));
 if(empty($interface)) $interface="eth1";

 // Récupère l’adresse IP actuelle du serveur
 $current_ip = trim(shell_exec("ip -4 addr show $interface | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
 if(empty($current_ip)) $current_ip="192.168.1.1";

 // Découpe l’adresse et prépare la nouvelle
 $parts = explode('.', $current_ip);
 $reseau = "$parts[0].$parts[1].$parts[2].0";
 $mask = "255.255.255.0";
 $passerelle = $current_ip;

 // Plage automatique en fonction du nombre d’appareils
 $start = 10;
 $end = $start + $n;
 $debut = "$parts[0].$parts[1].$parts[2].$start";
 $fin = "$parts[0].$parts[1].$parts[2].$end";

 // Application de la nouvelle IP (exemple)
 $new_ip = "$parts[0].$parts[1].$parts[2].16";
 shell_exec("sudo ifconfig $interface $new_ip netmask $mask up");

 echo "<h3>Mode automatique : $n appareils</h3>";
 echo "<pre>";
 echo ">> Interface détectée : $interface\n";
 echo ">> IP actuelle : $current_ip\n";
 echo ">> Nouvelle IP appliquée : $new_ip/24 avec gateway $passerelle\n";
 echo ">> Plage DHCP attribuée : $debut - $fin\n";
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

 echo "<h3>Mode manuel appliqué</h3>";
 echo "<pre>";
 echo ">> Réseau : $r\n";
 echo ">> Masque : $m\n";
 echo ">> Plage DHCP : $d - $f\n";
 echo ">> Passerelle : $p\n";
 echo shell_exec("sudo bash ../scripts/config_dhcp_auto.sh $r $m $d $f $p 2>&1");
 echo "</pre>";
}
?>
</body>
</html>
