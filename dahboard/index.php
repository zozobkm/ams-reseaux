<div class="main-content">
    <div class="header-page" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin-bottom: 5px;">Tableau de bord</h1>
            <p style="color: var(--text-muted);">Bienvenue, administrateur de la Box.</p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" class="btn-blue" style="background: <?= $is_avance ? '#f59e0b' : '#2563eb' ?>; border:none; padding: 10px 20px; border-radius: 8px; color: white; cursor: pointer;">
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
        <p>Le mode avancé est activé. Vous pouvez modifier les fichiers de configuration de la Box Ubuntu.</p>
    </div>
    <?php endif; ?>
</div>
