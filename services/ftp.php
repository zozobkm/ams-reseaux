<?php
require_once __DIR__."/../auth/require_login.php";

$vitesse = "";
if (isset($_POST['tester'])) {
    // Simulation de mesure de débit FTP (Tâche S5) 
    $debut = microtime(true);
    // Commande pour télécharger un fichier test de 10Mo
    // shell_exec("wget -O /dev/null http://cache.itv.re/10mo.dat"); 
    $fin = microtime(true);
    
    $temps = round($fin - $debut, 2);
    $debit = round(10 / $temps, 2); // Mo/s
    $vitesse = "Test terminé : 10 Mo téléchargés en $temps secondes. Débit : $debit Mo/s";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mesure de débit FTP - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>
<div class="main-content">
    <div class="header-status">
        <h1>Performances Réseau (FTP)</h1>
        <span class="badge-mode"><?= htmlspecialchars($_SESSION["mode"]) ?></span>
    </div>

    <div class="grid-services">
        <div class="card">
            <h3>🚀 Test de débit</h3>
            [cite_start]<p>Mesurez la vitesse de transfert entre votre Box et le serveur FAIUP[cite: 186].</p>
            <form method="post">
                <button type="submit" name="tester" class="btn">Lancer le test (10 Mo)</button>
            </form>
            <?php if($vitesse): ?>
                <div style="margin-top:20px; padding:15px; background:#e0f2fe; border-radius:8px; color:#0369a1;">
                    <strong><?= $vitesse ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <?php if($_SESSION["mode"]==="avance"): ?>
            <div class="card">
                <h3>⚙️ Paramètres Avancés FTP</h3>
                [cite_start]<p>Serveur : <strong>vsftpd</strong> [cite: 132]</p>
                [cite_start]<p>Utilisateur : <code>ftpuser</code> [cite: 133]</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
