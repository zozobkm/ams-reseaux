<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Déclaration des variables
$resultat = "";

// Si le formulaire est soumis pour configurer DNS
if (isset($_POST['configurer'])) {
    // Récupération du domaine DNS
    $domaine = trim($_POST['domaine']);
    
    // Exécution de la commande DNS
    $cmd = "sudo bash /var/www/html/ams-reseaux/scripts/config_dns.sh $domaine";
    $resultat = shell_exec($cmd . " 2>&1");
}

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
    <p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>

    <!-- Formulaire pour configurer le DNS -->
    <form method="post">
        <label for="domaine">Nom de domaine :</label>
        <input type="text" name="domaine" required><br><br>

        <button type="submit" name="configurer">Configurer</button>
    </form>

    <?php if ($resultat !== ""): ?>
        <!-- Affichage du résultat de la commande -->
        <div class="confirmation">
            <h3>Résultat de la configuration DNS :</h3>
            <pre><?= htmlspecialchars($resultat) ?></pre>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
