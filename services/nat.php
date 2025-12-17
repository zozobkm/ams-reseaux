<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Déclaration des variables
$resultat = "";

// Si le formulaire est soumis pour activer NAT
if (isset($_POST['activer_nat'])) {
    // Exécution de la commande NAT
    $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec($cmd . " 2>&1");
}

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
    <p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

    <!-- Formulaire de configuration NAT -->
    <form method="post">
        <button type="submit" name="activer_nat">Activer NAT</button>
    </form>

    <?php if ($resultat !== ""): ?>
        <!-- Affichage du résultat de la commande -->
        <div class="confirmation">
            <h3>Résultat de la configuration NAT :</h3>
            <pre><?= htmlspecialchars($resultat) ?></pre>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
