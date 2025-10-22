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
<form method="post">
<label>Nombre d'appareils :</label>
<input type="text" name="nb_appareils" placeholder="Ex : 5">
<input type="submit" name="auto" value="Appliquer">
</form>

<?php
if(isset($_POST['auto'])){
 $n=$_POST['nb_appareils'];
 echo "<pre>";
 echo shell_exec("sudo bash ../scripts/config_dhcp_auto.sh $n 2>&1");
 echo "</pre>";
}
?>
</body>
</html>
