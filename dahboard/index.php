<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

<?php include __DIR__ . '/../menu.php'; ?>

<div class="main-content">
    <div class="header-status">
        <div>
            <h1>Tableau de bord</h1>
            <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong> (<em><?= htmlspecialchars($_SESSION["role"]) ?></em>)</p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" style="background: var(--active-color);">
                Basculer en mode <?= $_SESSION["mode"] === "normal" ? "Avancé" : "Normal" ?>
            </button>
        </form>
    </div>

    <div class="grid-services">
        <a href="/ams-reseaux/services/forum.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>💬 Forum Entraide</h3>
            <p>Posez vos questions ou aidez la communauté.</p>
            <span class="badge-mode" style="background: #10b981;">Actif</span>
        </a>

        <a href="/ams-reseaux/services/dhcp.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📡 Service DHCP</h3>
            <p>Gestion de l'attribution des adresses IP locales.</p>
            <span class="badge-mode">Configuration</span>
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
            [cite_start]<p>Mesurez la vitesse réelle de votre connexion[cite: 135].</p>
        </a>

        <a href="/ams-reseaux/services/mail.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>📧 Serveur Mail</h3>
            [cite_start]<p>Accédez à votre messagerie locale Postfix[cite: 143, 144].</p>
        </a>
    </div>

    <?php if ($_SESSION["mode"] === "avance"): ?>
    <div class="card" style="margin-top: 30px; border-top: 4px solid var(--admin-color);">
        <h3>⚙️ Paramètres Avancés</h3>
        [cite_start]<p>En mode avancé, vous avez accès à la modification directe des fichiers de configuration système[cite: 165].</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
