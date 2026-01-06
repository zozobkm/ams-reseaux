<?php
session_start();
require_once 'db.php'; 

$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

// Logique de test : Insertion de données fictives pour la démo
if (isset($_POST['run_test'])) {
    $debit = rand(15, 45); 
    // On utilise les colonnes identifiées dans ton terminal
    $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (10, 5, ?)")->execute([$debit]);
    $message = "Test terminé avec succès !";
}

// Récupération de l'historique (Note : date_tes est le nom exact dans ta base)
$historique = $pdo->query("SELECT * FROM tests_debit ORDER BY date_tes DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - FTP & Débit</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include '../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>FTP & Mesure de Débit</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($_SESSION["mode"] ?? "Normal")) ?>
            </span>
        </div>

        <div class="card">
            <h3>🚀 Performance Réseau</h3>
            <p style="color: #555; margin-bottom: 20px;">
                Cette fonctionnalité permet de tester la bande passante réelle entre votre appareil et la CeriBox via un transfert FTP simulé.
            </p>
            
            <form method="post">
                <button type="submit" name="run_test" class="btn-blue">
                    Lancer un test de débit
                </button>
            </form>

            <?php if (isset($message)): ?>
                <p style="margin-top: 15px; color: #27ae60; font-weight: bold;">● <?= $message ?></p>
            <?php endif; ?>
        </div>

        

        <div class="card">
            <h3>📊 Historique des tests (SQL)</h3>
            <table style="width:100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 12px;">Date de mesure</th>
                        <th style="padding: 12px;">Taille</th>
                        <th style="padding: 12px;">Vitesse (Mbps)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historique)): ?>
                        <tr><td colspan="3" style="padding: 20px; text-align: center; color: #999;">Aucun test enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($historique as $t): ?>
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding: 12px;"><?= htmlspecialchars($t['date_tes']) ?></td>
                            <td style="padding: 12px; color: #7f8c8d;"><?= htmlspecialchars($t['taille_mo']) ?> Mo</td>
                            <td style="padding: 12px;"><strong style="color: #2c3e50;"><?= htmlspecialchars($t['debit_mbps']) ?> Mbps</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
