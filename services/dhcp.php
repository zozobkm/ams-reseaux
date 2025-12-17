<?php
require_once __DIR__."/../auth/require_login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DHCP</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="container">
    <h1>Configuration DHCP</h1>
    <p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

    <?php
    // Définir les valeurs par défaut pour la plage IP
    $ip_parts = explode(".", $_SERVER['SERVER_ADDR']);
    $default_start_ip = "{$ip_parts[0]}.{$ip_parts[1]}.{$ip_parts[2]}.10";
    $default_end_ip = "{$ip_parts[0]}.{$ip_parts[1]}.{$ip_parts[2]}.50";

    $resultat = "";

    // Si le formulaire de mode avancé est soumis
    if (isset($_POST['manuel'])) {
        $debut = trim($_POST['debut']);
        $fin = trim($_POST['fin']);

        if ($debut === "" || $fin === "") {
            $resultat = "<span style='color:red;'>Erreur : les champs ne peuvent pas être vides.</span>";
        } else {
            // Exécuter le script pour configurer le DHCP manuellement
            $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_manuel.sh ".
                   "192.168.1.0 255.255.255.0 $debut $fin 192.168.1.1";
            $log = shell_exec($cmd . " 2>&1");

            $resultat = "<b>Mode avancé appliqué :</b><br>Plage : $debut → $fin<br><pre>$log</pre>";
        }
    }

    // Si le formulaire de mode automatique est soumis
    if (isset($_POST['auto'])) {
        $nb = intval($_POST['nb']);
        if ($nb > 0) {
            $debut = $default_start_ip;
            $fin = "{$ip_parts[0]}.{$ip_parts[1]}.{$ip_parts[2]}." . (10 + $nb);
            
            // Exécuter le script pour configurer le DHCP automatiquement
            $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dhcp_auto.sh ".
                   "192.168.1.0 255.255.255.0 $debut $fin 192.168.1.1";
            $log = shell_exec($cmd . " 2>&1");

            $resultat = "<b>Mode automatique appliqué :</b><br>Plage : $debut → $fin<br><pre>$log</pre>";
        }
    }
    ?>

    <!-- Formulaire de configuration DHCP automatique -->
    <h2>Configuration DHCP automatique</h2>
    <form method="post">
        <label>Nombre d'appareils :</label>
        <input type="number" name="nb" min="1" required>
        <button type="submit" name="auto">Appliquer</button>
    </form>

    <hr>

    <!-- Formulaire de configuration DHCP manuelle (mode avancé) -->
    <h2>Configuration DHCP manuelle (mode avancé)</h2>
    <form method="post">
        <label>Début de la plage :</label>
        <input type="text" name="debut" value="<?= $default_start_ip ?>" required><br>

        <label>Fin de la plage :</label>
        <input type="text" name="fin" value="<?= $default_end_ip ?>" required><br><br>

        <button type="submit" name="manuel">Appliquer</button>
    </form>

    <!-- Affichage du résultat -->
    <?php if ($resultat !== ""): ?>
        <hr><div><?= $resultat ?></div>
    <?php endif; ?>
</div>

</body>
</html>
