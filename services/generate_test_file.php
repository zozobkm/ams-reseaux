<?php
// On vide le tampon de sortie pour envoyer les données au fur et à mesure
if (ob_get_level()) ob_end_clean();

header("Content-Type: application/octet-stream");
header("Content-Length: 10485760"); // 10 Mo exacts

// On envoie 10 Mo par blocs de 64 Ko pour ne pas saturer la RAM
for ($i = 0; $i < 160; $i++) {
    echo str_repeat("0", 65536); 
    flush(); // Envoie immédiatement au navigateur
}
?>
