<?php
session_start();
require_once __DIR__."/../config.php";
echo "DB FILE USED: " . realpath(__DIR__ . '/../forum/db.php');
exit;

if(isset($_SESSION["user_id"])){
    header("Location: /ams-reseaux/dashboard/index.php");
    exit;
}

$error="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $email=trim($_POST["email"]??"");
    $password=$_POST["password"]??"";

    if($email!=="" && $password!==""){
        $stmt=$pdo_box->prepare("SELECT id,email,password_hash,role FROM box_users WHERE email=?");
        $stmt->execute([$email]);
        $u=$stmt->fetch(PDO::FETCH_ASSOC);

        if($u && password_verify($password,$u["password_hash"])){
            $_SESSION["user_id"]=$u["id"];
            $_SESSION["email"]=$u["email"];
            $_SESSION["role"]=$u["role"];
            if(!isset($_SESSION["mode"])) $_SESSION["mode"]="normal";
            header("Location: /ams-reseaux/dashboard/index.php");
            exit;
        }else{
            $error="Identifiants invalides.";
        }
    }else{
        $error="Remplis tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body class="page">
<div class="card">
    <h1>Connexion Box</h1>
    <?php if($error!==""): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <p class="muted">Si tu n’as pas de compte, demande à l’admin.</p>
</div>
</body>
</html>
