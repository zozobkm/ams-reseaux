<?php
session_start();

/* Suppression du mode admin */
unset($_SESSION['admin']);

/* Redirection vers le forum */
header("Location: index.php");
exit;
