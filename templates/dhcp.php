
<?php include('menu.php'); ?>

<?php
// récupération IP eth1
$ip = trim(shell_exec("ip -4 addr show eth1 | grep inet | awk '{print $2}' | cut -d'/' -f1"));
$parts = explode(".", $ip);

// reconstruction du réseau automatiquement
$reseau = $parts[0] . "." . $parts[1] . "." . $parts[2] . ".0";
$passerelle = $parts[0] . "." . $parts[1] . "." . $parts[2] . ".1";

// Messages
$resultat = "";
$avancer = false;

// MODE AUTOMATIQUE
if (isset($_POST['auto'])) {
    $nb = intval($_POST['nb']);
    if ($nb > 0) {
        $debut = $parts[0] . "." . $parts[1] . "." . $parts[2] . ".10";
        $fin_num = 10 + $nb;
        $fin = $parts[0] . "." . $parts[1] . "." . $parts[2] . "." . $fin_num;

        $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh 
                $reseau 255.255.255.0 $debut $fin $passerelle";
        $resultat = shell_exec($cmd . " 2>&1");

        $resultat = "<b>Mode automatique appliqué :</b><br>
                     Plage générée : $debut → $fin <br><pre>$resultat</pre>";
    }
}

// MODE AVANCÉ
if (isset($_POST['manuel'])) {

    $d = trim($_POST['debut']);
    $f = trim($_POST['fin']);

    // Vérification basique
    if ($d === "" || $f === "") {
        $resultat = "<b style='color:red;'>Erreur : les champs ne peuvent pas être vides.</b>";
    } else {

        $parts = explode(".", $d);
        $reseau = $parts[0] . "." . $parts[1] . "." . $parts[2] . ".0";

        // masque fixe
        $masque = "255.255.255.0";
        $passerelle = $parts[0] . "." . $parts[1] . "." . $parts[2] . ".1";
        $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_manuel.sh
                $reseau $masque $d $f $passerelle";

        $log = shell_exec("$cmd 2>&1");
        $resultat = "
        <b>Mode avancé appliqué :</b><br>
        Réseau : $reseau<br>
        Plage : $d → $f <br>
        Passerelle : $passerelle<br>
        <pre>$log</pre>";
    }
}


?>

<h2>Configuration DHCP automatique</h2>
<form method="post">
    Nombre d'appareils :
    <input type="number" name="nb" min="1" required>
    <button type="submit" name="auto">Appliquer</button>
</form>

<hr>
<h2>Configuration manuelle (mode avancé)</h2>
<form method="post">
    <label>Début plage :</label>
    <input type="text" name="debut" placeholder="192.168.10.10" required><br>

    <label>Fin plage :</label>
    <input type="text" name="fin" placeholder="192.168.10.50" required><br><br>

    <button type="submit" name="manuel">Appliquer</button>
</form>

<?php
if ($resultat != "") echo "<hr><div>$resultat</div>";
?>

