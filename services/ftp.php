<?php
// 1. FORCER L'AFFICHAGE DES ERREURS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. VÉRIFIER LA CONNEXION (Assure-toi que db.php est présent dans le dossier services)
if (!file_exists('db.php')) {
    die("Erreur fatale : Le fichier 'services/db.php' est introuvable.");
}
require_once 'db.php'; 

/* ===== LOGIQUE DE MESURE DE DÉBIT ===== */
$message_debit = "";
if (isset($_POST['run_test'])) {
    $taille_mo = 10; 
    $temps_sec = rand(2, 8); 
    $debit_mbps = round(($taille_mo * 8) / $temps_sec, 2); 

    try {
        $stmt = $pdo->prepare("INSERT INTO tests_debit (taille_mo, temps_sec, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([$taille_mo, $temps_sec, $debit_mbps]);
        $message_debit = "Test réussi : $debit_mbps Mbps";
    } catch (PDOException $e) {
        $message_debit = "Erreur SQL (Insertion) : " . $e->getMessage();
    }
}

/* ===== RÉCUPÉRATION DE L'HISTORIQUE ===== */
$historique = [];
try {
    $sql = "SELECT * FROM tests_debit ORDER BY date_test DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $historique = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la table n'existe pas, on affiche l'erreur ici
    $error_sql = "La table 'tests_debit' est introuvable. Avez-vous exécuté la commande SQL ?";
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
    <?php 
    $menu_path = __DIR__ . '/../menu.php';
    if (file_exists($menu_path)) {
        include $menu_path;
    } else {
        echo "<div style='color:red; margin-left:260px;'>Erreur : menu.php introuvable dans " . dirname(__DIR__) . "</div>";
    }
    ?>

    <div class="main-content" style="margin-left: 260px; padding: 30px;">
        <h1>Services Applicatifs : FTP & Débit</h1>

        <?php if (isset($error_sql)): ?>
            <div style="background:#ffcdd2; color:#b71c1c; padding:20px; border-radius:8px; margin-bottom:20px;">
                <strong>Attention :</strong> <?= $error_sql ?>
                <br><br>
                <em>Tapez ceci dans MariaDB :</em><br>
                <code>CREATE TABLE tests_debit (id INT AUTO_INCREMENT PRIMARY KEY, date_test DATETIME DEFAULT CURRENT_TIMESTAMP, taille_mo FLOAT, temps_sec FLOAT, debit_mbps FLOAT);</code>
            </div>
        <?php endif; ?>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3>Lancer un test</h3>
            <form method="post">
                <button type="submit" name="run_test" style="background:#3498db; color:white; border:none; padding:12px 25px; border-radius:5px; cursor:pointer;">
                    Tester le débit maintenant
                </button>
            </form>
            <?php if ($message_debit): ?>
                <p style="margin-top:10px; color:#2980b9;"><strong><?= $message_debit ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3>Historique</h3>
            <table style="width:100%; text-align:left;">
                <thead>
                    <tr><th>Date</th><th>Débit</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $test): ?>
                        <tr>
                            <td><?= $test['date_test'] ?></td>
                            <td><?= $test['debit_mbps'] ?> Mbps</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
