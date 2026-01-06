<?php
session_start();
require_once __DIR__ . "/../config.php"; // 

// Redirection si déjà connecté
if(isset($_SESSION["user_id"])){
    header("Location: /ams-reseaux/dahboard/index.php");
    exit;
}

$error = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if($email !== "" && $password !== ""){
        $stmt = $pdo_box->prepare("SELECT id, email, password_hash, role FROM box_users WHERE email=?");
        $stmt->execute([$email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        // 🔹 UTILISATION DE PASSWORD_VERIFY POUR LA SÉCURITÉ
        if($u && password_verify($password, $u["password_hash"])){
            $_SESSION["user_id"] = $u["id"];
            $_SESSION["email"]   = $u["email"];
            $_SESSION["role"]    = $u["role"];
            if(!isset($_SESSION["mode"])) $_SESSION["mode"] = "normal"; // 

            header("Location: /ams-reseaux/dahboard/index.php");
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - CeriBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
    <style>
        /* Style spécifique pour centrer la carte de login car il n'y a pas de sidebar ici */
        body.login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #0f172a; /* Fond encore plus sombre pour le login */
            margin: 0;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .login-card h1 { text-align: center; color: #1e293b; margin-bottom: 30px; }
        .login-card input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .login-btn {
            width: 100%;
            background-color: #0284c7;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-card">
        <h1>CeriBOX</h1>
        <p style="text-align: center; color: #64748b; margin-top: -20px; margin-bottom: 30px;">Administration Réseau</p>

        <?php if($error !== ""): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="login-btn">Se connecter</button>
        </form>

        <p style="font-size: 0.85rem; color: #94a3b8; text-align: center; margin-top: 25px;">
            Accès restreint. Contactez l'administrateur pour obtenir un compte.
        </p>
    </div>
</body>
</html>
