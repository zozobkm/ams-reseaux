<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-brand">
        <h2>CeriBOX</h2>
        <span class="status-badge-mini" style="background: <?= ($mode === 'avance') ? '#f59e0b' : '#2563eb' ?>;">
            <?= strtoupper($_SESSION["mode"] ?? 'NORMAL') ?>
        </span>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/ams-reseaux/dahboard/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        
        <li class="menu-label">Services Réseaux</li>
        <li><a href="/ams-reseaux/services/dhcp.php"><i class="fas fa-network-wired"></i> DHCP</a></li>
        <li><a href="/ams-reseaux/services/dns.php"><i class="fas fa-server"></i> DNS</a></li>
        <li><a href="/ams-reseaux/services/nat.php"><i class="fas fa-shield-halved"></i> NAT / Firewall</a></li>
       <li><a href="/ams-reseaux/services/securite.php" class="menu-item"><i class="fas fa-user-shield"></i> Contrôle Parental</a></li>
        
        <li class="menu-label">Applications</li>
        <li><a href="/ams-reseaux/services/mail.php"><i class="fas fa-envelope"></i> Messagerie</a></li>
        <li><a href="/ams-reseaux/services/forum.php"><i class="fas fa-comments"></i> Forum Entraide</a></li>
        <li><a href="/ams-reseaux/services/ftp.php"><i class="fas fa-download"></i> Débit FTP</a></li>

        <li class="menu-divider"></li>
        <li>
            <a href="/ams-reseaux/auth/logout.php" style="color: #ef4444;">
                <i class="fas fa-power-off"></i> Déconnexion
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <i class="fas fa-user-circle"></i> <strong><?= htmlspecialchars($_SESSION["email"] ?? 'Invité') ?></strong>
    </div>
</div>
