<?php
session_start();
require_once 'db.php'; 

// Logique de test
if (isset($_POST['run_test'])) {
    $debit = rand(15, 45); 
    $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (10, 5, ?)")->execute([$debit]);
}

// Récupération (avec le nom de colonne date_tes)
$historique = $pdo->query("SELECT * FROM tests_debit ORDER BY date_tes DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - FTP</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include '../menu.php'; ?>
    <div class="main-content">
        <div class="header-page">
            <h1>FTP & Mesure de Débit</h1>
            <span class="badge">Service Actif</span>
        </div>
        <div class="card">
            <h3>Lancer un test de performance</h3>
            <form method="post"><button type="submit" name="run_test" class="btn-blue">Tester le débit</button></form>
        </div>
        <div class="card">
            <h3>Historique SQL</h3>
            <table style="width:100%; text-align:left;">
                <tr><th>Date (date_tes)</th><th>Débit</th></tr>
                <?php foreach ($historique as $t): ?>
                <tr><td><?= $t['date_tes'] ?></td><td><strong><?= $t['debit_mbps'] ?> Mbps</strong></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
