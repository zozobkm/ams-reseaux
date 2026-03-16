<?php
session_start();
// Vérification de connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

// Gestion des modes (Tâche S6)
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CeriBox - Dashboard</title>
    
    <link rel="stylesheet" href="../assets/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php 
// 3. Inclusion du menu (on remonte d'un dossier car on est dans /dahboard/)
$menu_path = __DIR__ . '/../menu.php';
if (file_exists($menu_path)) {
    include $menu_path;
} else {
    echo "<div style='margin-left:280px; color:red;'>Erreur : menu.php introuvable dans $menu_path</div>";
}
?>

<div class="main-content">
    <div class="header-page">
        <div>
            <h1>Tableau de bord</h1>
            <p style="color: var(--text-muted);">Bienvenue, <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong></p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" class="btn-blue" style="background: <?= $is_avance ? '#f59e0b' : '#2563eb' ?>;">
                <i class="fas <?= $is_avance ? 'fa-unlock' : 'fa-lock' ?>"></i> 
                Mode <?= $is_avance ? "Normal" : "Avancé" ?>
            </button>
        </form>
    </div>

    <div class="dashboard-grid">
        
        <a href="/ams-reseaux/services/forum.php" class="dashboard-card">
            <i class="fas fa-comments card-icon" style="color: #10b981;"></i>
            <h3>Forum Communautaire</h3>
            <p>Accédez aux discussions et entraidez les clients du FAI.</p>
        </a>

        <a href="/ams-reseaux/services/dhcp.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-network-wired card-icon"></i>
            <h3>Service DHCP</h3>
            <p>Gestion de l'attribution dynamique des adresses IP locales.</p>
        </a>

        <a href="/ams-reseaux/services/dns.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-database card-icon"></i>
            <h3>Service DNS</h3>
            <p>Résolution de noms et annuaire local du domaine box.local.</p>
        </a>

        <a href="/ams-reseaux/services/mail.php" class="dashboard-card">
            <i class="fas fa-envelope-open-text card-icon" style="color: #6366f1;"></i>
            <h3>Messagerie Postfix</h3>
            <p>Consultez et envoyez vos emails via le serveur local.</p>
        </a>

        <a href="/ams-reseaux/services/ftp.php" class="dashboard-card">
            <i class="fas fa-gauge-high card-icon" style="color: #ec4899;"></i>
            <h3>Débit & Performance</h3>
            <p>Tests de bande passante et transferts de fichiers FTP.</p>
        </a>

        <a href="/ams-reseaux/services/nat.php" class="dashboard-card <?= $is_avance ? 'expert-border' : '' ?>">
            <i class="fas fa-shield-virus card-icon"></i>
            <h3>Sécurité & NAT</h3>
            <p>Configuration du pare-feu et redirection de ports (PAT).</p>
        </a>
    </div>

    <?php if ($is_avance): ?>
    <div class="dashboard-card" style="margin-top: 30px; border: 1px dashed #f59e0b; background: #fffbeb;">
        <h3 style="color: #b45309;"><i class="fas fa-triangle-exclamation"></i> Administration Système</h3>
        <p>Le mode avancé est activé. Vous avez un accès direct aux réglages de la Box Ubuntu.</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
