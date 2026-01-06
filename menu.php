<?php
// Sécurité pour la session
if(session_status() === PHP_SESSION_NONE){ session_start(); }

// On récupère le mode pour l'affichage visuel
$current_mode = $_SESSION["mode"] ?? "normal";
$role = $_SESSION["role"] ?? "user";
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>CeriPBOX</h2>
        <span class="badge-mode"><?= htmlspecialchars(strtoupper($current_mode)) ?></span>
    </div>
    
    <nav class="nav-menu">
        <a href="/ams-reseaux/dahboard/index.php" class="nav-link">🏠 Dashboard</a>
        
        <div class="nav-divider"></div>
        
        <a href="/ams-reseaux/services/dhcp.php" class="nav-link">📡 Service DHCP</a>
        <a href="/ams-reseaux/services/dns.php" class="nav-link">📖 Service DNS</a>
        <a href="/ams-reseaux/services/nat.php" class="nav-link">🛡️ NAT / Internet</a>
        
        <div class="nav-divider"></div>

        <a href="/ams-reseaux/services/ftp.php" class="nav-link">🚀 Débit FTP</a>
        <a href="/ams-reseaux/services/mail.php" class="nav-link">📧 Messagerie</a>
        <a href="/ams-reseaux/services/forum.php" class="nav-link">💬 Forum Entraide</a>

        <div class="nav-divider"></div>

        <a href="/ams-reseaux/services/box_settings.php" class="nav-link">⚙️ Réglages IP</a>
        
        <?php if($role === "admin"): ?>
            <a href="/ams-reseaux/admin/users.php" class="nav-link admin-link">👮 Gestion Users</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <small>Connecté :</small><br>
            <strong><?= htmlspecialchars($_SESSION["email"] ?? "Invité") ?></strong>
        </div>
        <a class="logout-btn" href="/ams-reseaux/auth/logout.php">Déconnexion</a>
    </div>
</div>
