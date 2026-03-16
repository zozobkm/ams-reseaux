<?php
// Simule un fichier de 10Mo pour tester la bande passante
header("Content-Type: application/octet-stream");
echo str_repeat("0", 10 * 1024 * 1024);
?>
