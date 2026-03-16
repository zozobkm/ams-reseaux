<?php
require_once __DIR__."/../auth/require_login.php";
require_once 'db.php'; // Pour la cohérence avec tes autres pages

// --- 1. LOGIQUE D'ENVOI DE MAIL (NOUVEAU) ---
if (isset($_POST['envoyer_mail'])) {
    $dest = $_POST['destinataire'];
    $sujet = $_POST['sujet'];
    $msg = $_POST['message'];
    $headers = "From: admin@illipbox.lan\r\n" . "Reply-To: admin@illipbox.lan\r\n" . "X-Mailer: PHP/" . phpversion();

    // TRAITEMENT S6 : Filtrage de sécurité sur les chaînes
    $mots_suspects = ["virus", "pwned", "hack", "spam"];
    $msg_traite = str_ireplace($mots_suspects, "[CONTENU FILTRÉ]", $msg);

    if (mail($dest, $sujet, $msg_traite, $headers)) {
        $feedback = "<div class='card' style='border-left: 5px solid #27ae60;'>✅ Mail envoyé avec succès à <strong>$dest</strong> (Traitement S6 appliqué).</div>";
        // Archivage S6 : On logue l'envoi dans ton fichier d'audit
        shell_exec("echo \"$(date '+%Y-%m-%d %H:%M:%S') | MAIL_OUT | $dest\" >> /home/stud/ftp_audit.log");
    } else {
        $feedback = "<div class='card' style='border-left: 5px solid #e74c3c;'>❌ Échec de l'envoi. Vérifiez les logs Postfix.</div>";
    }
}

// --- 2. LOGIQUE DE CRÉATION DE COMPTE (MODÉRATION) ---
if (isset($_POST['creer_compte']) && $_SESSION["mode"] === "avance") {
    $nouveau_user = escapeshellarg(trim($_POST['nom_utilisateur']));
    $res = shell_exec("sudo /var/www/html/ams-reseaux/scripts/config_mail.sh add $nouveau_user 2>&1");
    $feedback = "<div class='card' style='border-left: 5px solid #3498db;'>$res</div>";
}

$status_postfix = shell_exec("systemctl is-active postfix");
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";

$comptes_mail = [];
if ($is_avance) {
    $output = shell_exec("cut -d: -f1 /etc/passwd | getent passwd | awk -F: '$3 >= 1000 {print $1}'");
    $comptes_mail = explode("\n", trim($output));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Messagerie Postfix</title>
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>Serveur de Mail (Postfix)</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($_SESSION["mode"])) ?>
            </span>
        </div>

        <?php if (isset($feedback)) echo $feedback; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3>🛡️ État du Système</h3>
                <p>Service : <strong>Postfix</strong></p>
                <p>Statut : 
                    <?php if(trim($status_postfix) === "active"): ?>
                        <span style="color: #27ae60; font-weight: bold;">● Opérationnel</span>
                    <?php else: ?>
                        <span style="color: #e74c3c; font-weight: bold;">● Arrêté / Erreur</span>
                    <?php endif; ?>
                </p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                <p style="font-size: 0.85em; color: #7f8c8d;">Protocoles : SMTP (Port 25)</p>
            </div>

            <div class="card">
                <h3>✉️ Envoi rapide (Test)</h3>
                <form method="post">
                    <input type="email" name="destinataire" placeholder="Destinataire (ex: stud@illipbox.lan)" required 
                           style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <input type="text" name="sujet" placeholder="Sujet" required 
                           style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <textarea name="message" placeholder="Votre message..." required 
                              style="width: 100%; height: 60px; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                    <button type="submit" name="envoyer_mail" class="btn-blue" style="width: 100%;">Envoyer le mail</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>📧 Accès Webmail</h3>
            <p>Utilisez Rainloop pour une gestion complète de vos messages.</p>
            <div style="margin-top: 10px;">
                <a href="/rainloop" target="_blank" class="btn-blue" style="text-decoration: none; display: inline-block; background: #34495e;">
                    Ouvrir Rainloop
                </a>
            </div>
        </div>

        <?php if ($is_avance): ?>
            <div class="card" style="margin-top: 25px;">
                <h3>👥 Comptes système (Mail local)</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #eee;">
                            <th style="padding: 12px;">Utilisateur</th>
                            <th style="padding: 12px;">Adresse @illipbox.lan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comptes_mail as $user): if(!$user || $user == 'root') continue; ?>
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding: 12px;"><strong><?= htmlspecialchars($user) ?></strong></td>
                            <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($user) ?>@illipbox.lan</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="post" style="margin-top: 25px; padding-top: 20px; border-top: 1px dashed #ddd;">
                    <label style="font-weight: bold; display: block; margin-bottom: 10px;">Créer un nouvel utilisateur système :</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="nom_utilisateur" placeholder="ex: zohra" required style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; flex: 1;">
                        <button type="submit" name="creer_compte" class="btn-blue" style="background: #2c3e50;">Ajouter</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
