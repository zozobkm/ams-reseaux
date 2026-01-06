<?php
session_start();
// Vérification de la session
if (!isset($_SESSION["user_id"])) {
    header("Location: /ams-reseaux/auth/login.php");
    exit;
}

// Détection du mode pour le style dynamique
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Tableau de bord</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Grille pour organiser les services proprement */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        /* Style pour rendre les cartes cliquables */
        .card-link {
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../menu.php'; ?>

<div class="main-content">
    
    <div class="header-page">
        <div>
            <h1>Tableau de bord</h1>
            <p style="color: #64748b;">Bienvenue, <strong><?= htmlspecialchars($_SESSION["email"]) ?></strong> 
               <span class="badge" style="background: #94a3b8; font-size: 0.7em; vertical-align: middle;">
                   <?= htmlspecialchars(strtoupper($_SESSION["role"])) ?>
               </span>
            </p>
        </div>
        
        <form method="post" action="toggle_mode.php">
            <button type="submit" class="btn-blue" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Passer en mode <?= $is_avance ? "Normal" : "Avancé" ?>
            </button>
        </form>
    </div>

    <div class="grid-container">
        
        <a href="/ams-reseaux/services/forum.php" class="card card-link">
            <div>
                <h3>💬 Forum Entraide</h3>
                <p>Posez vos questions ou aidez la communauté sur le réseau local.</p>
            </div>
            <div style="margin-top: 15px;"><span class="badge" style="background: #27ae60;">Actif</span></div>
        </a>

        <a href="/ams-reseaux/services/dhcp.php" class="card card-link">
            <div>
                <h3>📡 Service DHCP</h3>
                <p>Gestion de l'attribution des adresses IP locales de vos appareils.</p>
            </div>
            <div style="margin-top: 15px;"><span class="badge">Configuration</span></div>
        </a>

        <a href="/ams-reseaux/services/dns.php" class="card card-link">
            <div>
                <h3>📖 Service DNS</h3>
                <p>Gestion des noms de domaine et de l'annuaire local (Bind9).</p>
            </div>
        </a>

        <a href="/ams-reseaux/services/nat.php" class="card card-link">
            <div>
                <h3>🛡️ Sécurité & NAT</h3>
                <p>Partage de connexion internet et redirection de ports (Firewall).</p>
            </div>
        </a>

        <a href="/ams-reseaux/services/ftp.php" class="card card-link">
            <div>
                <h3>🚀 Débit FTP</h3>
                <p>Mesurez la vitesse réelle de votre connexion via le serveur FTP.</p>
            </div>
        </a>

        <a href="/ams-reseaux/services/mail.php" class="card card-link">
            <div>
                <h3>📧 Serveur Mail</h3>
                <p>Accédez à votre messagerie locale sécurisée Postfix.</p>
            </div>
        </a>
    </div>

    <?php if ($is_avance): ?>
    <div class="card" style="margin-top: 30px; border-left: 5px solid #e67e22;">
        <h3 style="color: #e67e22;">⚙️ Administration Avancée</h3>
        <p>Vous avez actuellement un accès privilégié pour modifier directement les fichiers de configuration système (.conf).</p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
