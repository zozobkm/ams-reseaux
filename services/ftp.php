<?php
session_start();
require_once 'db.php'; 

$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";
$logFile = "/home/stud/ftp_audit.log";

// --- GESTION DU BOUTON : LANCEMENT DU SCRIPT RÉEL ---
if (isset($_POST['run_test'])) {
    // Exécution du script Bash et capture du texte de sortie
    $output = shell_exec("bash /var/www/html/ams-reseaux/scripts/config_ftp.sh upload 2>&1");
    
    // Extraction de la vitesse pour le SQL via une expression régulière
    preg_match('/Debit : ([\d\.]+) Mo\/s/', $output, $matches);
    $debit_detecte = isset($matches[1]) ? (float)$matches[1] : 0;

    if ($debit_detecte > 0) {
        $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (10, 0, ?)")->execute([$debit_detecte]);
        $message = "Test effectué avec succès : $debit_detecte Mo/s.";
    } else {
        $message = "Erreur de connexion FTP (Code 530). Donnée loguée pour analyse S6.";
    }
}

$historique = $pdo->query("SELECT * FROM tests_debit ORDER BY date_tes DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Cyber-Sentinel S6</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include '../menu.php'; ?>

    <div class="main-content">
        <h1>Surveillance Flux & Débit</h1>

       
        <div class="card" style="border-left: 5px solid #e74c3c;">
            <h3>🛡️ Analyseur de Sécurité</h3>
            <?php
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $total = 0; $count = 0; $lastVal = 0;

                foreach ($lines as $l) {
                    $parts = explode(" | ", trim($l)); // Traitement de chaînes
                    if (count($parts) == 3) {
                        $val = (float)$parts[2];
                        $total += $val; $count++;
                        $lastVal = $val;
                    }
                }
                $moyenne = $count > 0 ? $total / $count : 0;

                // ALGORITHME : Détection si chute > 50% de la moyenne
                if ($lastVal < ($moyenne * 0.5) && $count > 1) {
                    echo "<div style='color:red;'><strong>⚠️ ANOMALIE :</strong> Débit actuel ($lastVal Mo/s) très inférieur à la moyenne (".round($moyenne, 2)." Mo/s).</div>";
                } else {
                    echo "<div style='color:green;'><strong>✅ NOMINAL :</strong> Flux réseau stable.</div>";
                }
            }
            ?>
        </div>

        <div class="card">
            <h3>Lancer une mesure réelle</h3>
            <form method="post">
                <button type="submit" name="run_test" class="btn-blue">Démarrer le test FTP</button>
            </form>
            <?php if (isset($message)) echo "<p>$message</p>"; ?>
        </div>

        <div class="card">
            <h3> Historique SQL</h3>
            <table style="width:100%;">
                <?php foreach ($historique as $t): ?>
                    <tr>
                        <td><?= $t['date_tes'] ?></td>
                        <td><strong><?= $t['debit_mbps'] ?> Mo/s</strong></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
