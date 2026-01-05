<?php
require_once __DIR__."/../auth/require_login.php";
require_once "db.php"; // Pour accéder à $pdo

$msg_resultat = "";

if (isset($_POST['tester'])) {
    // 1. Simulation du test (ou appel du script bash)
    $debut = microtime(true);
    // Ici on simule un transfert de 10Mo
    usleep(800000); // Simule une attente de 0.8s
    $fin = microtime(true);
    
    $temps = $fin - $debut;
    $vitesse = round(10 / $temps, 2); // Mo/s
    
    // 2. Enregistrement en base de données
    $stmt = $pdo->prepare("INSERT INTO tests_debit (vitesse, type_test) VALUES (?, 'download')");
    $stmt->execute([$vitesse]);
    
    $msg_resultat = "Test réussi : $vitesse Mo/s";
}

// 3. Récupération de l'historique (les 5 derniers)
$historique = $pdo->query("SELECT * FROM tests_debit ORDER BY date_test DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Débit FTP - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="main-content">
    <h1>Mesure de débit FTP</h1>

    <div class="grid-services">
        <div class="card">
            <h3>🚀 Lancer un test</h3>
            <p>Testez la vitesse de téléchargement depuis le serveur FAIUP.</p>
            <form method="post">
                <button type="submit" name="tester" class="btn">Démarrer le test (10 Mo)</button>
            </form>
            <?php if($msg_resultat): ?>
                <p style="margin-top:15px; color:#10b981; font-weight:bold;"><?= $msg_resultat ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>📊 Historique récent</h3>
            <?php if(empty($historique)): ?>
                <p>Aucun test effectué pour le moment.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid #ddd;">
                            <th>Date</th>
                            <th>Vitesse</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($historique as $test): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:8px 0;"><?= date('d/m H:i', strtotime($test['date_test'])) ?></td>
                                <td style="padding:8px 0;"><strong><?= $test['vitesse'] ?> Mo/s</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
