<?php
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["nb_appareils"])){
    $nb = intval($_POST["nb_appareils"]);
    if($nb>0){
        // Variables réseau
        $reseau = "192.168.10.0";
        $masque = "255.255.255.0";
        $passerelle = "192.168.10.1";
        $debut = "192.168.10.10";
        $fin_val = 10 + $nb;
        $fin = "192.168.10.$fin_val";

        // Exécution du script automatique
        $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh $reseau $masque $debut $fin $passerelle 2>&1";
        $output = shell_exec($cmd);

        // Vérifie le statut du service DHCP
        $status = shell_exec("systemctl is-active isc-dhcp-server");

        echo "<h2 style='color:green'>DHCP automatique configuré pour $nb appareils</h2>";
        echo "<p><b>Réseau :</b> $reseau / <b>Masque :</b> $masque</p>";
        echo "<p><b>Plage attribuée :</b> $debut → $fin</p>";
        echo "<p><b>Passerelle :</b> $passerelle</p>";
        echo "<p><b>Statut du service :</b> <span style='color:".($status=='active'?'green':'red')."'>$status</span></p>";
        echo "<pre>$output</pre>";
    } else {
        echo "<p style='color:red'> Nombre d'appareils invalide.</p>";
    }
}
?>

<h2>Configuration DHCP automatique</h2>
<form method="post">
    <label>Nombre d'appareils :</label>
    <input type="number" name="nb_appareils" min="1" max="50" required>
    <input type="submit" value="Appliquer">
</form>
