<?php
try{
    $pdo = new PDO(
        "mysql:host=localhost;dbname=box;charset=utf8",
        "forumuser",
        "forum123"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("Erreur DB : ".$e->getMessage());
}
