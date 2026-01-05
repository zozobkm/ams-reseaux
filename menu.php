<?php
if(session_status()===PHP_SESSION_NONE){session_start();}
// On récupère le mode actuel pour l'affichage
$current_mode = $_SESSION["mode"] ?? "normal";
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>ILLIPBOX</h2>
        <span class="badge-mode"><?= htmlspecialchars($current_mode) ?></span>
    </div>
    
    <nav class="nav-menu">
        <a href="/ams-reseaux/dahboard/index.php" class="nav-link">🏠 Dashboard</a>
        <a href="/ams-reseaux/services/dhcp.php" class="nav-link">📡 DHCP</a>
        <a href="/ams-reseaux/services/dns.php" class="nav-link">📖 DNS</a>
        <a href="/ams-reseaux/services/nat.php" class="nav-link">🛡️ NAT</a>
        <a href="/ams-reseaux/services/ftp.php" class="nav-link">📂 FTP / Débit</a>
        <a href="/ams-reseaux/services/mail.php" class="nav-link">📧 Mail</a>
        <a href="/ams-reseaux/services/forum.php" class="nav-link">💬 Forum</a>

        <?php if(isset($_SESSION["role"]) && $_SESSION["role"]==="admin"): ?>
            <div class="nav-divider"></div>
            <a href="/ams-reseaux/admin/users.php" class="nav-link admin-link">⚙️ Administration</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a class="logout-btn" href="/ams-reseaux/auth/logout.php">Déconnexion</a>
    </div>
</div>
