<?php
// services/security.php

function filtrer_censure($texte) {
    // Ta liste de mots interdits (Tâche S6)
    $mots_interdits = ["hack", "virus", "crack", "password", "root", "prout"];
    
    // Le remplacement "pro"
    $remplacement = "<span class='censored' style='color:red; font-weight:bold;'>[CENSURÉ]</span>";
    
    // On filtre sans tenir compte de la casse (HACK = hack)
    return str_ireplace($mots_interdits, $remplacement, $texte);
}
?>
