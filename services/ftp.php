<?php
session_start();
require_once 'db.php'; 

$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";
$logFile = "/home/stud/ftp_audit.log"; # Chemin du log S6

// Logique de test : Simulation de lancement du script Bash
if (isset($_POST['run_test'])) {
    // Ici on simule l'insertion SQL, mais le script Bash remplit le fichier log en parallèle
    $debit = rand(15, 45); 
    $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (10, 5, ?)")->execute([$debit]);
    $message = "Test terminé avec succès ! Données archivées pour analyse S6.";
}

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

       
        <div class="card" style="border-left: 5px solid #e74c3c; background: #fff5f5;">
            <h3>🛡️ Analyse de Sécurité (Tâche S6)</h3>
            <?php
            if (file_exists($logFile)) {
                $data = file($logFile);
                $total = 0; $count = 0;
                $lastSpeed = 0;

                foreach ($data as $line) {
                    $parts = explode(" | ", trim($line)); // Traitement de chaîne
                    if (count($parts) == 3) {
                        $speed = (float)$parts[2];
                        $total += $speed;
                        $count++;
                        $lastSpeed = $speed;
                    }
                }

                $moyenne = $count > 0 ? $total / $count : 0;

                if ($lastSpeed < ($moyenne * 0.5) && $count > 1) {
                    echo "<p style='color: #e74c3c; font-weight: bold;'>⚠️ ALERTE : Débit anormalement bas détecté !</p>";
                    echo "<p>Moyenne historique : <strong>".round($moyenne, 2)." Mo/s</strong> | Actuel : <strong style='color:red;'>$lastSpeed Mo/s</strong></p>";
                } else {
                    echo "<p style='color: #27ae60; font-weight: bold;'>✅ État du réseau : Nominal</p>";
                    echo "<p>Le débit actuel ($lastSpeed Mo/s) est conforme à votre moyenne historique.</p>";
                }
            } else {
                echo "<p>En attente de données pour l'analyse S6...</p>";
            }
            ?>
        </div>

        <div class="card">
            <h3>🚀 Performance Réseau</h3>
            <form method="post">
                <button type="submit" name="run_test" class="btn-blue">Lancer un test de débit</button>
            </form>
            <?php if (isset($message)): ?>
                <p style="margin-top: 15px; color: #27ae60; font-weight: bold;">● <?= $message ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>📊 Historique SQL (Base de données)</h3>
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 12px;">Date</th>
                        <th style="padding: 12px;">Vitesse (Mbps)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $t): ?>
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 12px;"><?= htmlspecialchars($t['date_tes']) ?></td>
                        <td style="padding: 12px;"><strong><?= htmlspecialchars($t['debit_mbps']) ?> Mbps</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
