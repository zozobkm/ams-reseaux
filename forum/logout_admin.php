<?php
session_start();
unset($_SESSION['admin']);
header("Location: /ams-reseaux/services/forum.php");
exit;
