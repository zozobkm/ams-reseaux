<?php
session_start();
require_once 'db.php';

// On vérifie que l'utilisateur est connecté et que le contenu n'est pas vide
if (isset($_SESSION['user_id']) && !empty($_POST['contenu'])) {
    
    $user_id = $_SESSION['user_id'];
    $contenu = trim($_POST['contenu']);

    try {
        $sql = "INSERT INTO messages (user_id, contenu, date_post) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $contenu]);
        
        // Redirection vers le forum après succès
        header("Location: forum.php?status=success");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'envoi : " . $e->getMessage());
    }
} else {
    // Si pas de session ou contenu vide
    header("Location: forum.php?status=error");
    exit();
}
