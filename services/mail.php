<?php
require_once __DIR__."/../auth/require_login.php";

$status_postfix = shell_exec("systemctl is-active postfix");
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

// Simulation de récupération des comptes mails locaux (lecture de /etc/passwd ou dossier mail)
$comptes_mail = [];
if ($is_avance) {
    // Cette commande liste les utilisateurs réels du système qui peuvent avoir un mail
    $output = shell_exec("cut -d: -f1 /etc/passwd | getent passwd | awk -F: '$3 >= 1000 {print $1}'");
    $comptes_mail = explode("\n", trim($output));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie Interne - ILLIPBOX</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>
<?php include __DIR__."/../menu.php"; ?>

<div class="main-content">
    <div class="header-status">
        <h1>Serveur de Mail (Postfix)</h1>
        <span class="badge-mode"><?= htmlspecialchars($_SESSION["mode"]) ?></span>
    </div>

    <div class="grid-services">
        <div class="card">
            <h3>🛡️ État du Système</h3>
            <p>Service : <strong>Postfix</strong></p>
            <p>Statut : 
                <?php if(trim($status_postfix) === "active"): ?>
                    <span style="color: #10b981; font-weight: bold;">● Opérationnel</span>
                <?php else: ?>
                    <span style="color: #ef4444; font-weight: bold;">● Arrêté</span>
                <?php endif; ?>
            </p>
            <hr>
            <p>Protocoles activés : <strong>SMTP, POP3, IMAP</strong></p>
        </div>

        <div class="card">
            <h3>📧 Accès aux messages</h3>
            <p>Utilisez l'interface Rainloop ou Roundcube pour lire vos mails.</p>
            <a href="/rainloop" target="_blank" class="logout-btn" style="background: var(--active-color); text-decoration: none;">
                Ouvrir le Webmail
            </a>
        </div>
    </div>

    <?php if ($is_avance): ?>
        <div class="card" style="margin-top: 25px;">
            <h3>👥 Comptes Mail sur la Box</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 10px;">Utilisateur</th>
                        <th style="padding: 10px;">Adresse Mail</th>
                        <th style="padding: 10px;">Espace utilisé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comptes_mail as $user): if(!$user) continue; ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px;"><?= htmlspecialchars($user) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($user) ?>@illipbox.lan</td>
                        <td style="padding: 10px;">--</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
