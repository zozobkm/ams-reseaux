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
input[type=text]{width:100%;padding:5px}
input[type=submit]{margin-top:15px;background:#0078d7;color:white;border:none;padding:8px 15px;border-radius:5px;cursor:pointer}
input[type=submit]:hover{background:#005fa3}
pre{background:#eee;padding:10px;text-align:left;border-radius:5px}
</style>
</head>
<body>

<h2>Modification de l'adresse IP (interface eth1)</h2>

<?php
$file = '/etc/network/interfaces';

// Lire le fichier et extraire l'adresse IP actuelle
$current_ip = trim(shell_exec("grep 'address' $file | awk '{print \$2}'"));
if(empty($current_ip)) $current_ip = "192.168.1.1";

echo "<p>Adresse IP actuelle : <strong>$current_ip</strong></p>";

// Si l'utilisateur a soumis le formulaire
if(isset($_POST['new_ip'])){
    $new_ip = trim($_POST['new_ip']);

    // Vérifie que seuls les deux derniers octets changent
    $old_parts = explode('.', $current_ip);
    $new_parts = explode('.', $new_ip);

    if($old_parts[0] == $new_parts[0] && $old_parts[1] == $new_parts[1]){
        echo "<pre>Changement d'adresse IP en cours...</pre>";

        // Modifier la ligne dans le fichier interfaces
        $cmd = "sudo sed -i 's/address .*/address $new_ip/' $file";
        shell_exec($cmd);

        // Redémarre juste eth1 sans reboot total
        shell_exec("sudo ifdown eth1 && sudo ifup eth1");

        echo "<pre>Adresse IP modifiée avec succès : $new_ip</pre>";
    } else {
        echo "<pre>Erreur : tu ne peux changer que les deux derniers nombres !</pre>";
    }
}
?>

<form method="post">
<label>Nouvelle adresse IP :</label>
<input type="text" name="new_ip" placeholder="ex : 192.168.1.13">
<input type="submit" value="Modifier">
</form>

</body>
</html>
