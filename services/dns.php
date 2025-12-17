<?php
require_once __DIR__ . "/../auth/require_login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration DNS</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . "/../menu.php"; ?>

<div class="container">
    <h1>Configuration DNS</h1>

    <form method="post">
        <label for="domaine">Nom de domaine :</label>
        <input type="text" name="domaine" required><br><br>

        <button type="submit" name="configurer">Configurer</button>
    </form>

    <?php
    if (isset($_POST['configurer'])) {
        $domaine = trim($_POST['domaine']);
        $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dns.sh $domaine";
        $resultat = shell_exec($cmd . " 2>&1");
        echo "<pre>$resultat</pre>";
    }
    ?>

</div>

</body>
</html>
