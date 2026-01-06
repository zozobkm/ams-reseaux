<div class="sidebar">
    <div class="sidebar-brand">
        <h2>CeriBOX</h2>
        <span class="status-badge-mini"><?= htmlspecialchars($_SESSION["mode"] ?? 'NORMAL') ?></span>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/ams-reseaux/dahboard/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>
        <li class="menu-label">Services Réseaux</li>
        <li>
            <a href="/ams-reseaux/services/dhcp.php">Service DHCP</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/dns.php">Service DNS</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/nat.php">NAT / Internet</a>
        </li>
        <li class="menu-label">Applications</li>
        <li>
            <a href="/ams-reseaux/services/ftp.php">Débit FTP</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/mail.php">Messagerie</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/forum.php">Forum Entraide</a>
        </li>
        <li class="menu-divider"></li>
        <li>
            <a href="/ams-reseaux/services/reglages.php">Réglages IP</a>
        </li>
        <li>
            <a href="/ams-reseaux/auth/logout.php" style="color: #ff7675;">Déconnexion</a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        Connecté : <strong><?= htmlspecialchars($_SESSION["email"] ?? 'Invité') ?></strong>
    </div>
</div>
