<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once __DIR__ . "/../services/db.php";

// On récupère le nom de l'utilisateur Linux (ex: alice depuis alice@box.local)
$user_email = $_SESSION['email'];
$user_linux = explode('@', $user_email)[0];
$file_path = "/var/mail/" . $user_linux;

$message_sent = "";

// --- GESTION DE L'ENVOI (Tâche S5) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_mail'])) {
    $to = escapeshellcmd($_POST['to']);
    $subject = escapeshellcmd($_POST['subject']);
    $msg = $_POST['message'];

    // Envoi via la commande mail de Linux (Postfix)
    $command = "echo " . escapeshellarg($msg) . " | mail -s " . escapeshellarg($subject) . " " . escapeshellarg($to);
    shell_exec($command);
    $message_sent = "<div class='badge success'>✉️ Message envoyé à $to !</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Messagerie FAI</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Style spécifique pour les mails */
        .mail-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .mail-card { 
            background: white; border-left: 5px solid #3498db; 
            margin-bottom: 15px; padding: 15px; border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }
        .mail-header { 
            border-bottom: 1px solid #eee; padding-bottom: 8px; 
            margin-bottom: 10px; font-size: 0.9em; color: #7f8c8d;
        }
        .mail-subject { display: block; font-weight: bold; color: #2c3e50; font-size: 1.1em; }
        .mail-body { color: #34495e; line-height: 1.5; white-space: pre-wrap; }
        .censored { color: #e74c3c; font-weight: bold; background: #fdeaea; padding: 0 4px; border-radius: 3px; }
        .badge { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <div class="main-content">
        <h1>📧 Messagerie Postfix</h1>
        <p>Utilisateur : <strong><?= htmlspecialchars($user_email) ?></strong></p>
        
        <?= $message_sent ?>

        <div class="mail-container">
            <div class="card">
                <h3>🆕 Nouveau Message</h3>
                <form method="post">
                    <input type="text" name="to" placeholder="Destinataire (ex: bob@localhost)" required style="width:100%; margin-bottom:10px; padding:10px;">
                    <input type="text" name="subject" placeholder="Sujet" required style="width:100%; margin-bottom:10px; padding:10px;">
                    <textarea name="message" rows="6" placeholder="Votre message..." required style="width:100%; margin-bottom:10px; padding:10px;"></textarea>
                    <button type="submit" name="send_mail" class="btn-blue" style="width:100%;">Envoyer le mail</button>
                </form>
            </div>

            <div class="card">
                <h3>📥 Boîte de réception</h3>
                <?php
                if (file_exists($file_path) && filesize($file_path) > 0) {
                    $content = file_get_contents($file_path);
                    // On découpe le fichier mbox par les lignes commençant par "From "
                    $emails = explode("\nFrom ", $content);

                    foreach (array_reverse($emails) as $email) {
                        if (empty(trim($email))) continue;

                        // Extraction du Sujet
                        preg_match('/Subject: (.*)/i', $email, $sub_match);
                        $subject = $sub_match[1] ?? '(Sans sujet)';

                        // Extraction de l'expéditeur
                        preg_match('/From: (.*)/i', $email, $from_match);
                        $sender = $from_match[1] ?? 'Inconnu';

                        // Extraction du corps (après les headers)
                        $parts = preg_split("/\n\s*\n/", $email, 2);
                        $body = isset($parts[1]) ? trim($parts[1]) : "Contenu vide";

                        // --- TÂCHE S6 : FILTRAGE DE SÉCURITÉ ---
                        $mots_interdits = ["hack", "virus", "crack", "password", "root"];
                        $body_filtre = str_ireplace(
                            $mots_interdits, 
                            "<span class='censored'>[CENSURÉ]</span>", 
                            htmlspecialchars($body)
                        );
                        ?>
                        <div class="mail-card">
                            <div class="mail-header">
                                <span class="mail-subject"><?= htmlspecialchars($subject) ?></span>
                                <span>De : <?= htmlspecialchars($sender) ?></span>
                            </div>
                            <div class="mail-body"><?= nl2br($body_filtre) ?></div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color:#7f8c8d; text-align:center;'>Votre boîte est vide. 📭</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
