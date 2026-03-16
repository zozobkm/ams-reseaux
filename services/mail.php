<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// --- INITIALISATION DES RÔLES ---
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true; // Patron (Gestion comptes/forum)
$is_expert = (isset($_SESSION['mode']) && $_SESSION['mode'] === "avance"); // Client Avancé (Config réseau)
$currentUserEmail = $_SESSION['email']; 
$currentUsername = explode('@', $currentUserEmail)[0]; // On récupère 'user1' de 'user1@box.local'

// --- 1. LOGIQUE D'ENVOI (Pour TOUS les utilisateurs) ---
if (isset($_POST['envoyer_mail'])) {
    $dest = trim($_POST['destinataire']);
    $sujet = htmlspecialchars($_POST['sujet']);
    $msg = $_POST['message'];
    
    // Traitement S6 : Censure de sécurité avant envoi
    $mots_interdits = ["virus", "hack", "spam"];
    $msg_filtre = str_ireplace($mots_interdits, "[CENSURÉ]", $msg);
    
    $headers = "From: $currentUserEmail\r\n" . "Reply-To: $currentUserEmail\r\n";

    if (mail($dest, $sujet, $msg_filtre, $headers)) {
        $feedback = "<div class='card' style='border-left: 5px solid #27ae60;'>✅ Message envoyé à $dest</div>";
        // Audit S6 : Enregistrement de l'activité [cite: 17]
        shell_exec("echo \"$(date) | MAIL_OUT | From: $currentUsername To: $dest\" >> /home/stud/ftp_audit.log");
    }
}

// --- 2. LOGIQUE DE RÉCEPTION (Lecture de la session actuelle) ---
$mailBoxFile = "/var/mail/" . $currentUsername;
$boite_reception = [];
if (file_exists($mailBoxFile)) {
    $content = file_get_contents($mailBoxFile);
    $boite_reception = explode("From ", $content);
    array_shift($boite_reception); // On retire l'entête vide
}

// --- 3. LOGIQUE ADMIN : CRÉATION DE COMPTE (Patron uniquement) ---
if (isset($_POST['creer_user']) && $is_admin) {
    $nouveau = escapeshellarg(trim($_POST['nom_user']));
    // Appel du script de configuration mail S5 
    $res = shell_exec("sudo /var/www/html/ams-reseaux/scripts/config_mail.sh add $nouveau 2>&1");
    $feedback = "<div class='card' style='border-left: 5px solid #e67e22;'>⚙️ Admin : $res</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Messagerie</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>Messagerie Postfix</h1>
            <div>
                <span class="badge" style="background: #3498db;">Session : <?= $currentUsername ?></span>
                <?php if($is_expert): ?><span class="badge" style="background: #e67e22;">MODE EXPERT</span><?php endif; ?>
                <?php if($is_admin): ?><span class="badge" style="background: #c0392b;">ADMIN</span><?php endif; ?>
            </div>
        </div>

        <?php if (isset($feedback)) echo $feedback; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3>✉️ Nouveau Message</h3>
                <form method="post">
                    <input type="text" name="destinataire" placeholder="Destinataire (ex: user2@localhost)" required style="width:100%; margin-bottom:10px; padding:8px;">
                    <input type="text" name="sujet" placeholder="Sujet" required style="width:100%; margin-bottom:10px; padding:8px;">
                    <textarea name="message" placeholder="Votre message..." style="width:100%; height:80px; padding:8px;"></textarea>
                    <button type="submit" name="envoyer_mail" class="btn-blue" style="width:100%; margin-top:10px;">Envoyer</button>
                </form>
            </div>

            <div class="card">
                <h3>📥 Boîte de réception de <?= $currentUsername ?></h3>
                <div style="max-height: 250px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 5px;">
                    <?php if (empty($boite_reception)): ?>
                        <p style="font-style: italic; color: #94a3b8;">Aucun message.</p>
                    <?php else: ?>
                        <?php foreach (array_reverse($boite_reception) as $m): ?>
                            <div style="border-bottom: 1px solid #ddd; padding: 10px 0; font-size: 0.85em;">
                                <pre style="white-space: pre-wrap;"><?= htmlspecialchars(substr($m, 0, 300)) ?></pre>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($is_expert): ?>
        <div class="card" style="margin-top:20px; border-left: 5px solid #e67e22;">
            <h3>🛠️ Configuration Expert (Postfix)</h3>
            <p>Statut du service : <strong><?= shell_exec("systemctl is-active postfix") ?></strong></p>
            [cite_start]<p style="font-size: 0.9em;">Protocoles supportés : POP3, IMAP, SMTP [cite: 38]</p>
        </div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <div class="card" style="margin-top:20px; border-left: 5px solid #c0392b;">
            <h3>👤 Administration : Création de compte Mail</h3>
            <form method="post" style="display: flex; gap: 10px;">
                <input type="text" name="nom_user" placeholder="Nom du nouveau client (ex: alice)" required style="flex:1; padding:8px;">
                <button type="submit" name="creer_user" class="btn-blue" style="background: #c0392b;">Créer le compte système</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
