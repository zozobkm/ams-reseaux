<?php
session_start();

// On retire uniquement les droits d'admin
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

// On redirige vers le forum en mode normal
header("Location: forum.php");
exit();
?>
