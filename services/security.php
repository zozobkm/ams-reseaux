<?php
// services/security.php

function filtrer_censure($texte) {
    // Ta liste de mots interdits 
    $mots_interdits = ["hack", "virus", "crack", "password", "root"];
  
    $remplacement = "<span class='censored' style='color:red; font-weight:bold;'>[CENSURÉ]</span>";
    
    return str_ireplace($mots_interdits, $remplacement, $texte);
}
?>
