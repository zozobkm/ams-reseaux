<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
    <title>Configuration DHCP</title>
</head>
<?php include('menu.php'); ?>

<?php
// ---- RÉCUPÉRATION IP ETH1 ----
$ip = trim(shell_exec("ip -4 addr show eth1 | grep inet | awk '{print $2}' | cut -d'/' -f1"));
$parts = explode(".", $ip);

$reseau = "{$parts[0]}.{$parts[1]}.{$parts[2]}.0";
$passerelle = "{$parts[0]}.{$parts[1]}.{$parts[2]}.1";
$masque = "255.255.255.0";

// valeurs par défaut
$debut_defaut = "{$parts[0]}.{$parts[1]}.{$parts[2]}.10";
$fin_defaut   = "{$parts[0]}.{$parts[1]}.{$parts[2]}.50";

$resultat = "";

// ================= MODE AUTO ===================
if (isset($_POST['auto'])) {

    $nb = intval($_POST['nb']);
    if ($nb > 0) {

        $debut = $debut_defaut;
        $fin_num = 10 + $nb;
        $fin = "{$parts[0]}.{$parts[1]}.{$parts[2]}.$fin_num";

        $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh "
             . "$reseau $masque $debut $fin $passerelle";

        $log = shell_exec("$cmd 2>&1");

        $resultat = "
        <b>Mode automatique appliqué :</b><br>
        Plage : $debut → $fin<br>
        <pre>$log</pre>
        ";
    }
}

// ================= MODE MANUEL ===================
if (isset($_POST['manuel'])) {

    $d = trim($_POST['debut']);
    $f = trim($_POST['fin']);

    if ($d === "" || $f === "") {
        $resultat = "<span style='color:red;'>Erreur : champs vides</span>";
    } else {

        $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_manuel.sh "
             . "$reseau $masque $d $f $passerelle";

        $log = shell_exec("$cmd 2>&1");

        $resultat = "
        <b>Mode avancé appliqué :</b><br>
        Réseau : $reseau<br>
        Plage : $d → $f<br>
        Passerelle : $passerelle<br>
        <pre>$log</pre>
        ";
    }
}
?>

<h2>Configuration DHCP automatique</h2>

<form method="post">
    <label>Nombre d'appareils :</label>
    <input type="number" name="nb" min="1" required>
    <button type="submit" name="auto">Appliquer</button>
</form>

<hr>

<h2>Configuration DHCP manuelle (mode avancé)</h2>

<form method="post">
    <label>Début de plage :</label>
    <input type="text" name="debut" value="<?php echo $debut_defaut; ?>" required><br>

    <label>Fin de plage :</label>
    <input type="text" name="fin" value="<?php echo $fin_defaut; ?>" required><br><br>

    <button type="submit" name="manuel">Appliquer</button>
</form>

<?php 
if ($resultat) {
    echo "<hr>$resultat";
}
?>
</html>
