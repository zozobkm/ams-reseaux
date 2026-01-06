<?php
// 1. Démarrer la session pour accéder aux variables existantes
session_start();

// 2. Supprimer uniquement la variable 'admin'
// Cela permet de quitter le "Mode Expert" sans déconnecter l'utilisateur de la Box
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

// 3. Redirection immédiate vers le forum
// L'utilisateur verra alors l'interface simplifiée (Mode Normal)
header('Location: forum.php');
exit();
?>
