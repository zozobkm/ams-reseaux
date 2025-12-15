<?php
require_once __DIR__."/../auth/require_login.php";
if(($_SESSION["role"]??"user")!=="admin"){
    header("Location: /ams-reseaux/dashboard/index.php");
    exit;
}
require_once __DIR__."/../config.php";

$msg="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $email=trim($_POST["email"]??"");
    $password=$_POST["password"]??"";
    $role=$_POST["role"]??"user";
    if($role!=="admin") $role="user";

    if($email!=="" && $password!==""){
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $stmt=$pdo_box->prepare("INSERT INTO box_users(email,password_hash,role) VALUES(?,?,?)");
        try{
            $stmt->execute([$email,$hash,$role]);
            $msg="Utilisateur créé.";
        }catch(Exception $e){
            $msg="Erreur: ".$e->getMessage();
        }
    }else{
        $msg="Champs manquants.";
    }
}

$users=$pdo_box->query("SELECT id,email,role,created_at FROM box_users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Admin Users</title><link rel="stylesheet" href="/ams-reseaux/assets/style.css"></head>
<body>
<?php include __DIR__."/../menu.php"; ?>
<div class="container">
<h1>Admin - Utilisateurs</h1>

<?php if($msg!==""): ?><div class="card"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
<h2>Créer un compte</h2>
<form method="post">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Mot de passe" required>
  <select name="role">
    <option value="user">user</option>
    <option value="admin">admin</option>
  </select>
  <button type="submit">Créer</button>
</form>
</div>

<div class="card">
<h2>Liste</h2>
<?php foreach($users as $u): ?>
  <p>#<?= (int)$u["id"] ?> - <?= htmlspecialchars($u["email"]) ?> (<?= htmlspecialchars($u["role"]) ?>)</p>
<?php endforeach; ?>
</div>

</div>
</body>
</html>
