<?php
session_start();
if(isset($_SESSION["user_id"])){
    header("Location: /ams-reseaux/dashboard/index.php");
}else{
    header("Location: /ams-reseaux/auth/login.php");
}
exit;
