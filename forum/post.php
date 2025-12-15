<?php
require_once 'db.php';

$username = trim($_POST['username']);
$contenu = trim($_POST['contenu']);

if($username === '' || $contenu === ''){
    die("Champs invalides");
}

/* vérifier si l'utilisateur existe */
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if(!$user){
    $stmt = $pdo->prepare("INSERT INTO users(username,password) VALUES(?, '')");
    $stmt->execute([$username]);
    $user_id = $pdo->lastInsertId();
}else{
    $user_id = $user['id'];
}

/* insertion du message */
$stmt = $pdo->prepare("INSERT INTO messages(user_id,contenu) VALUES(?,?)");
$stmt->execute([$user_id, $contenu]);

header("Location: index.php");
exit;
