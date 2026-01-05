<?php
require_once __DIR__."/../auth/require_login.php"; // Vérification connexion
if(($_SESSION["role"]??"user")!=="admin"){
    header("Location: /ams-reseaux/dahboard/index.php"); // Redirige si pas admin
    exit;
}
require_once __DIR__."/../config.php"; // Accès DB

$msg="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $email=trim($_POST["email"]??"");
    $password=$_POST["password"]??"";
    $role=$_POST["role"]??"user";
    if($role!=="admin") $role="user";

    if($email!=="" && $password!==""){
        $hash=password_hash($password,PASSWORD_DEFAULT); // Sécurité mot de passe
        $stmt=$pdo_box->prepare("INSERT INTO box_users(email,password_hash,role) VALUES(?,?,?)");
        try{
            $stmt->execute([$email,$hash,$role]);
            $msg="Succès : Utilisateur créé.";
        }catch(Exception $e){
            $msg="Erreur technique : ".$e->getMessage();
        }
    }else{
        $msg="Attention : Veuillez remplir tous les champs.";
    }
}

$users=$pdo_box->query("SELECT id,email,role,created_at FROM box_users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration des Comptes - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php include __DIR__."/../menu.php"; ?> <div class="main-content">
    <div class="header-status">
        <h1>Gestion des Utilisateurs</h1>
        <span class="badge-mode">Admin</span>
    </div>

    <?php if($msg!==""): ?>
        <div class="card" style="border-left: 5px solid var(--active-color); margin-bottom: 20px;">
            <p><?= htmlspecialchars($msg) ?></p>
        </div>
    <?php endif; ?>

    <div class="grid-services">
        <div class="card">
            <h3>➕ Créer un nouvel accès</h3>
            <form method="post" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                <input type="email" name="email" placeholder="Adresse Email" required 
                       style="padding: 10px; border-radius: 5px; border: 1px solid #cbd5e1;">
                
                <input type="password" name="password" placeholder="Mot de passe" required 
                       style="padding: 10px; border-radius: 5px; border: 1px solid #cbd5e1;">
                
                <select name="role" style="padding: 10px; border-radius: 5px; border: 1px solid #cbd5e1;">
                    <option value="user">Utilisateur Standard</option>
                    <option value="admin">Administrateur</option>
                </select>
                
                <button type="submit" class="logout-btn" style="background: var(--active-color); border: none;">
                    Enregistrer l'utilisateur
                </button>
            </form>
        </div>

        <div class="card">
            <h3>👥 Comptes existants</h3>
            <div style="margin-top: 15px;">
                <?php foreach($users as $u): ?>
                    <div style="padding: 10px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                        <span><strong><?= htmlspecialchars($u["email"]) ?></strong></span>
                        <span class="badge-mode" style="background: <?= $u['role'] === 'admin' ? 'var(--admin-color)' : '#64748b' ?>;">
                            <?= htmlspecialchars($u["role"]) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
