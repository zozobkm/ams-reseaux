<?php
// Génère 10 Mo de données pour simuler un vrai transfert
header("Content-Type: application/octet-stream");
header("Content-Length: " . (10 * 1024 * 1024));
echo str_repeat("0", 10 * 1024 * 1024);
?>
