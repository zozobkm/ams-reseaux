<?php
session_start();
if(isset($_SESSION["user_id"])){
    header("Location: /ams-reseaux/dahboard/index.php");
}else{
    header("Location: /ams-reseaux/auth/login.php");
}
exit;
