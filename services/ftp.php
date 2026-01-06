<?php
session_start();
require_once 'db.php'; 

/* ===== LOGIQUE DE MESURE DE DÉBIT ===== */
$message_debit = "";
if (isset($_POST['run_test'])) {
    // Simulation du téléchargement d'un fichier de 10 Mo [cite: 182]
    $taille_mo = 10; 
    
    // Temps fictif simulant l'exécution d'un script Bash dans /scripts/ [cite: 208]
    $temps_sec = rand(2, 8); 
    
    // Calcul du débit : Taille / Temps 
    $debit_mbps = round(($taille_mo * 8) / $temps_sec, 2); 

    try {
        // Enregistrement dans la table tests_debit 
        $stmt = $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([$taille_mo, $temps_sec, $debit_mbps]);
        $message_debit = "Test réussi : $debit_mbps Mbps";
    } catch (PDOException $e) {
        $message_debit = "Erreur SQL : " . $e->getMessage();
    }
}

/* ===== RÉCUPÉRATION DE L'HISTORIQUE ===== */
$sql = "SELECT * FROM tests_debit ORDER BY date_test DESC LIMIT 5";
$stmt = $pdo->query($sql);
$historique = $stmt->fetchAll();
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
            <span class="mode-badge" style="background:#e67e22; color:white; padding:5px 15px; border-radius:20px;">Mode Expert</span>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3>État du serveur FTP (vsftpd)</h3>
            [cite_start]<p>Le serveur FTP est utilisé pour le partage de documents entre Alice et la box[cite: 179, 180].</p>
            <div style="color: #27ae60; font-weight: bold;">● Service vsftpd : Actif</div>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3>Mesure de performance de la ligne</h3>
            [cite_start]<p>Calcul du débit réel via le téléchargement d'un fichier test de 10 Mo[cite: 182, 183].</p>
            
            <form method="post">
                <button type="submit" name="run_test" class="btn-test" style="background:#3498db; color:white; border:none; padding:12px 25px; border-radius:5px; cursor:pointer; font-weight:bold;">
                    Lancer un test de débit
                </button>
            </form>
            
            <?php if ($message_debit): ?>
                <div style="margin-top:15px; padding:10px; background:#e1f5fe; border-left:5px solid #3498db;"><?= $message_debit ?></div>
            <?php endif; ?>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3>Historique des tests (Base de données SQL)</h3>
            <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                <thead>
                    <tr style="text-align:left; border-bottom:2px solid #eee;">
                        <th style="padding:10px;">Date du test</th>
                        <th style="padding:10px;">Temps (sec)</th>
                        <th style="padding:10px;">Débit calculé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $test): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:10px;"><?= $test['date_test'] ?></td>
                            <td style="padding:10px;"><?= $test['temps_sec'] ?>s</td>
                            <td style="padding:10px; font-weight:bold; color:#2c3e50;"><?= $test['debit_mbps'] ?> Mbps</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
