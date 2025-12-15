<?php
$host = "localhost";
$dbname = "box";         
$user = "forumuser";      
$pass = "test"; 

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("Erreur DB BOX : " . $e->getMessage());
}
