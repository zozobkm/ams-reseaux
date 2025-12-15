<?php
session_start();
session_destroy();
header("Location: /ams-reseaux/auth/login.php");
exit;

