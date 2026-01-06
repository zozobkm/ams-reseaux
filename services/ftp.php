<?php
// 1. On affiche les erreurs au cas où
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php'; 

$message_debit = "";

/* ===== LOGIQUE DE TEST DE DÉBIT ===== */
if (isset($_POST['run_test'])) {
    $taille_mo = 10; 
    $temps_sec = rand(2, 6); // Simulation du temps de téléchargement
    $debit_mbps = round(($taille_mo * 8) / $temps_sec, 2); 

    try {
        // Insertion dans la table que tu viens de créer
        $stmt = $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([$taille_mo, $temps_sec, $debit_mbps]);
        $message_debit = "Succès ! Débit mesuré : " . $debit_mbps . " Mbps";
    } catch (PDOException $e) {
        $message_debit = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

/* ===== RÉCUPÉRATION DE L'HISTORIQUE ===== */
$historique = [];
try {
    $sql = "SELECT * FROM tests_debit ORDER BY date_test DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $historique = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_sql = "Erreur de lecture de la table.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - FTP & Débit</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content" style="margin-left: 260px; padding: 30px;">
        <div class="header">
            <h1>Services Applicatifs : FTP & Débit</h1>
            <span class="mode-badge" style="background:#3498db; color:white; padding:5px 15px; border-radius:20px;">Mode Normal</span>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 25px;">
            <h3>Lancer une mesure de débit</h3>
            <p>Ce test simule le téléchargement d'un fichier de 10 Mo via le protocole FTP pour calculer la vitesse de votre ligne.</p>
            
            <form method="post">
                <button type="submit" name="run_test" style="background:#3498db; color:white; border:none; padding:12px 25px; border-radius:5px; cursor:pointer; font-weight:bold;">
                    Tester le débit maintenant
                </button>
            </form>

            <?php if ($message_debit): ?>
                <div style="margin-top:20px; padding:15px; background:#e8f5e9; color:#2e7d32; border-radius:5px; font-weight:bold;">
                    <?= $message_debit ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            <h3>Historique des 5 derniers tests</h3>
            <?php if (empty($historique)): ?>
                <p style="color:gray; font-style:italic;">Aucun test enregistré pour le moment. Cliquez sur le bouton ci-dessus !</p>
            <?php else: ?>
                <table style="width:100%; border-collapse: collapse; margin-top:10px;">
                    <thead>
                        <tr style="text-align:left; border-bottom:2px solid #f4f7f6;">
                            <th style="padding:10px;">Date et Heure</th>
                            <th style="padding:10px;">Débit (Mbps)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $test): ?>
                            <tr style="border-bottom:1px solid #f4f7f6;">
                                <td style="padding:10px;"><?= $test['date_test'] ?></td>
                                <td style="padding:10px; font-weight:bold; color:#2c3e50;"><?= $test['debit_mbps'] ?> Mbps</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
