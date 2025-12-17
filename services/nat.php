<?php
require_once __DIR__ . "/../auth/require_login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration NAT</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . "/../menu.php"; ?>

<div class="container">
    <h1>Configuration NAT</h1>

    <form method="post">
        <button type="submit" name="activer_nat">Activer NAT</button>
    </form>

    <?php
    if (isset($_POST['activer_nat'])) {
        $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_nat.sh";
        $resultat = shell_exec($cmd . " 2>&1");
        echo "<pre>$resultat</pre>";
    }
    ?>

</div>

</body>
</html>
