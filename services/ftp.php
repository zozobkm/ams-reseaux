<?php
// 1. Affichage des erreurs pour le débug
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php'; 

$message_debit = "";

/* ===== LOGIQUE DE MESURE DE DÉBIT ===== */
if (isset($_POST['run_test'])) {
    $taille_mo = 10; 
    $temps_sec = rand(2, 6); 
    $debit_mbps = round(($taille_mo * 8) / $temps_sec, 2); 

    try {
        // Insertion dans la table avec les colonnes de ta capture
        $stmt = $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([$taille_mo, $temps_sec, $debit_mbps]);
        $message_debit = "Test terminé : " . $debit_mbps . " Mbps";
    } catch (PDOException $e) {
        $message_debit = "Erreur SQL : " . $e->getMessage();
    }
}

/* ===== RÉCUPÉRATION DE L'HISTORIQUE ===== */
$historique = [];
try {
    // Utilisation du nom de colonne 'date_tes' vu dans ton terminal
    $sql = "SELECT * FROM tests_debit ORDER BY date_tes DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $historique = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_sql = "Erreur de lecture : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - FTP & Débit</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .main-content { margin-left: 260px; padding: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; border-bottom: 2px solid #eee; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .btn-test { background: #3498db; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Gestion FTP & Mesure de Débit</h1>

        <div class="card">
            <h3>Calculer la vitesse de connexion</h3>
            <p>Ce test simule un transfert FTP pour mesurer les performances de votre CeriBox.</p>
            <form method="post">
                <button type="submit" name="run_test" class="btn-test">Lancer le test de débit</button>
            </form>
            <?php if ($message_debit): ?>
                <p style="margin-top:15px; color:#2980b9; font-weight:bold;"><?= $message_debit ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Historique des mesures (Base SQL)</h3>
            <?php if (isset($error_sql)): ?>
                <p style="color:red;"><?= $error_sql ?></p>
            <?php elseif (empty($historique)): ?>
                <p>Aucun test dans l'historique.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date (date_tes)</th>
                            <th>Taille (Mo)</th>
                            <th>Temps (s)</th>
                            <th>Débit (Mbps)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars($test['date_tes']) ?></td>
                                <td><?= htmlspecialchars($test['taille_mo']) ?> Mo</td>
                                <td><?= htmlspecialchars($test['temps_sec']) ?> s</td>
                                <td style="font-weight:bold; color:#27ae60;"><?= htmlspecialchars($test['debit_mbps']) ?> Mbps</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
