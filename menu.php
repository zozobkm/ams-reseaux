<div class="sidebar">
    <div class="sidebar-brand">
        <h2>CeriBOX</h2>
        <span class="status-badge-mini"><?= htmlspecialchars($_SESSION["mode"] ?? 'NORMAL') ?></span>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/ams-reseaux/dahboard/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <span>🏠</span> Dashboard
            </a>
        </li>
        <li class="menu-label">Services Réseaux</li>
        <li>
            <a href="/ams-reseaux/services/dhcp.php"><span>📡</span> Service DHCP</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/dns.php"><span>📖</span> Service DNS</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/nat.php"><span>🛡️</span> NAT / Internet</a>
        </li>
        <li class="menu-label">Applications</li>
        <li>
            <a href="/ams-reseaux/services/ftp.php"><span>🚀</span> Débit FTP</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/mail.php"><span>📧</span> Messagerie</a>
        </li>
        <li>
            <a href="/ams-reseaux/services/forum.php"><span>💬</span> Forum Entraide</a>
        </li>
        <li class="menu-divider"></li>
        <li>
            <a href="/ams-reseaux/services/reglages.php"><span>⚙️</span> Réglages IP</a>
        </li>
        <li>
            <a href="/ams-reseaux/auth/logout.php" style="color: #ff7675;"><span>🚪</span> Déconnexion</a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        Connecté : <strong><?= htmlspecialchars($_SESSION["email"] ?? 'Invité') ?></strong>
    </div>
</div>
