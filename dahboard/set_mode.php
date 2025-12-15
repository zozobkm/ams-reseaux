<?php
require_once __DIR__."/../auth/require_login.php";

$mode=$_GET["mode"]??"normal";
if($mode!=="normal" && $mode!=="avance") $mode="normal";
$_SESSION["mode"]=$mode;

header("Location: /ams-reseaux/dahboard/index.php");
exit;
