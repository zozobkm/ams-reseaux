<?php
require_once __DIR__ . '/../auth/require_login.php';
if(($_SESSION["role"]??"user")!=="admin"){
    header("Location: /ams-reseaux/forum/index.php");
    exit;
}

require_once __DIR__ . '/db.php';

$id=(int)($_POST["id"]??0);
if($id>0){
    $stmt=$pdo->prepare("DELETE FROM messages WHERE id=?");
    $stmt->execute([$id]);
}
header("Location: /ams-reseaux/forum/index.php");
exit;
