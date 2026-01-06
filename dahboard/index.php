<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Dashboard</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php 
// Inclusion du menu (il est à la racine du projet ams-reseaux)
$menu_path = __DIR__ . '/../menu.php';
if (file_exists($menu_path)) {
    include $menu_path;
}
?>

<div class="main-content">
    <div class="header-page">
        <div>
            <h1>Tableau de bord</h1>
            <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong> (<em><?= htmlspecialchars($_SESSION["role"]) ?></em>)</p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" class="btn-blue" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Passer en mode <?= $is_avance ? "Normal" : "Avancé" ?>
            </button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        
        <a href="/ams-reseaux/services/forum.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>💬 Forum Entraide</h3>
            <p>Posez vos questions ou aidez la communauté.</p>
            <span class="badge" style="background: #27ae60;">Actif</span>
        </a>

        <a href="/ams-reseaux/services/dhcp.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📡 Service DHCP</h3>
            <p>Gestion de l'attribution des adresses IP locales.</p>
            <span class="badge">Configuration</span>
        </a>

        <a href="/ams-reseaux/services/dns.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📖 Service DNS</h3>
            <p>Gestion des noms de domaine et de l'annuaire.</p>
        </a>

        <a href="/ams-reseaux/services/nat.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>🛡️ Sécurité & NAT</h3>
            <p>Partage de connexion et redirection de ports.</p>
        </a>

        <a href="/ams-reseaux/services/ftp.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📂 Débit FTP</h3>
            <p>Mesurez la vitesse réelle de votre connexion.</p>
        </a>

        <a href="/ams-reseaux/services/mail.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📧 Serveur Mail</h3>
            <p>Accédez à votre messagerie locale Postfix.</p>
        </a>
    </div>

    <?php if ($is_avance): ?>
    <div class="card" style="margin-top: 30px; border-left: 5px solid #e67e22;">
        <h3>⚙️ Paramètres Avancés</h3>
        <p>En mode avancé, vous avez accès à la modification directe des fichiers système.</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
