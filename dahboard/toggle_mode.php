<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

$_SESSION["mode"] = ($_SESSION["mode"] === "normal") ? "avance" : "normal";
header("Location: /ams-reseaux/dahboard/index.php");
exit;
