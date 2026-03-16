<?php
require_once 'db.php';
session_start();

if (isset($_POST['debit'])) {
    try {
        // On insère les données. La colonne date_tes se remplira toute seule.
        $stmt = $pdo->prepare("INSERT INTO tests_debit (temps_sec, taille_mo, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['temps'] ?? 0, 
            $_POST['taille'] ?? 10, 
            $_POST['debit']
        ]);
        echo "OK";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Erreur";
    }
}
