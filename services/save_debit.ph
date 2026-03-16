<?php
require_once 'db.php';
session_start();

if (isset($_POST['debit']) && isset($_POST['temps']) && isset($_POST['taille'])) {
    try {
        // Insertion précise selon ton DESCRIBE
        $sql = "INSERT INTO tests_debit (temps_sec, taille_mo, debit_mbps) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['temps'],  // Correspond à temps_sec
            $_POST['taille'], // Correspond à taille_mo
            $_POST['debit']   // Correspond à debit_mbps
        ]);
        echo "Données insérées avec succès.";
    } catch (PDOException $e) {
        echo "Erreur SQL : " . $e->getMessage();
    }
}
?>
