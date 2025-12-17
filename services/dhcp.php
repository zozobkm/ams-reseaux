<?php
require_once __DIR__ . "/../auth/require_login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration DHCP</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__ . "/../menu.php"; ?>

<div class="container">
    <h1>Configuration DHCP</h1>
    
    <!-- Affichage du mode actuel -->
    <p>Vous êtes actuellement en mode : <strong><?= htmlspecialchars($_SESSION["mode"] === "avance" ? "Avancé" : "Normal") ?></strong></p>
    
    <hr>
    
    <!-- Bouton pour passer du mode Normal au mode Avancé -->
    <form method="post" action="toggle_mode.php">
        <button type="submit">
            Passer en mode <?= $_SESSION["mode"] === "normal" ? "Avancé" : "Normal" ?>
        </button>
    </form>

    <hr>

    <!-- Si en mode avancé, afficher les options avancées -->
    <?php if ($_SESSION["mode"] === "avance"): ?>
        <div class="card">
            <h3>Configuration Avancée de votre réseau (DHCP)</h3>
            <p>Dans ce mode, vous pouvez définir précisément la plage d'adresses IP attribuées à vos appareils.</p>
            <p>Assurez-vous de bien comprendre vos besoins avant de configurer.</p>
        </div>
    <?php endif; ?>

    <!-- Zone normale avec options simples -->
    <div class="card">
        <h3>Configurer la plage d’adresses pour vos appareils</h3>
        <p>Si vous ne savez pas ce que c’est, laissez les paramètres par défaut.</p>

        <form method="post">
            <?php if ($_SESSION["mode"] === "normal"): ?>
                <!-- Mode Normal -->
                <p>Le serveur DHCP attribuera des adresses IP à vos appareils dans cette plage : <strong>192.168.1.10 à 192.168.1.50</strong></p>
                <button type="submit" name="auto">Appliquer</button>
            <?php elseif ($_SESSION["mode"] === "avance"): ?>
                <!-- Mode Avancé -->
                <label for="debut">Plage de début :</label>
                <input type="text" name="debut" value="192.168.1.10" required>
                <br><br>
                <label for="fin">Plage de fin :</label>
                <input type="text" name="fin" value="192.168.1.50" required>
                <br><br>
                <button type="submit" name="manuel">Appliquer</button>
            <?php endif; ?>
        </form>
    </div>

    <hr>

    <!-- Résultat de la configuration -->
    <?php if ($resultat !== ""): ?>
        <div>
            <h4>Configuration DHCP appliquée</h4>
            <?= $resultat ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
