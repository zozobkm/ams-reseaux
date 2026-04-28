<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once __DIR__ . "/../services/db.php";
require_once __DIR__ . "/../services/security.php"; 

$user_email = $_SESSION['email'];
$user_linux = explode('@', $user_email)[0];
$file_path = "/var/mail/" . $user_linux;

$message_sent = "";

// --- ENVOI ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_mail'])) {
    $to = escapeshellcmd($_POST['to']);
    $subject = escapeshellcmd($_POST['subject']);
    $msg = $_POST['message'];
    $command = "echo " . escapeshellarg($msg) . " | mail -s " . escapeshellarg($subject) . " " . escapeshellarg($to);
    shell_exec($command);
    $message_sent = "<div class='badge success'>✉️ Message envoyé à $to !</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Messagerie</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .mail-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; margin-top: 20px; }
        .mail-card { background: white; border-left: 5px solid #3498db; margin-bottom: 15px; padding: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .mail-header { border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 10px; font-size: 0.9em; display: flex; justify-content: space-between; }
        .mail-subject { font-weight: bold; color: #2c3e50; }
        .mail-body { color: #34495e; line-height: 1.5; }
        .censored { color: #e74c3c; font-weight: bold; background: #fdeaea; padding: 0 4px; border-radius: 3px; } /* Style pour le mot censuré */
        .badge { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <div class="main-content">
        <h1> Messagerie Postfix</h1>
        <?= $message_sent ?>

        <div class="mail-container">
            <div class="card">
                <h3> Nouveau Message</h3>
                <form method="post">
                    <input type="text" name="to" placeholder="Destinataire (ex: bob@localhost)" required style="width:100%; margin-bottom:10px; padding:10px;">
                    <input type="text" name="subject" placeholder="Sujet" required style="width:100%; margin-bottom:10px; padding:10px;">
                    <textarea name="message" rows="6" placeholder="Votre message..." required style="width:100%; margin-bottom:10px; padding:10px;"></textarea>
                    <button type="submit" name="send_mail" class="btn-blue" style="width:100%;">Envoyer</button>
                </form>
            </div>

            <div class="card">
                <h3> Boîte de réception</h3>
                <?php
                if (file_exists($file_path) && filesize($file_path) > 0) {
                    $content = file_get_contents($file_path);
                    $emails = explode("\nFrom ", $content);

                    foreach (array_reverse($emails) as $email) {
                        if (empty(trim($email))) continue;

                        preg_match('/Subject: (.*)/i', $email, $sub_match);
                        $subject = $sub_match[1] ?? '(Sans sujet)';

                        preg_match('/From: (.*)/i', $email, $from_match);
                        $sender = $from_match[1] ?? 'Inconnu';

                        $parts = preg_split("/\n\s*\n/", $email, 2);
                        $body = isset($parts[1]) ? trim($parts[1]) : "Contenu vide";

                        $body_propre = htmlspecialchars($body);
                 
                        $body_final = filtrer_censure($body_propre);
                        ?>
                        
                        <div class="mail-card">
                            <div class="mail-header">
                                <span class="mail-subject"><?= htmlspecialchars($subject) ?></span>
                                <span>De : <?= htmlspecialchars($sender) ?></span>
                            </div>
                            <div class="mail-body"><?= nl2br($body_final) ?></div>
                        </div>

                        <?php
                    }
                } else {
                    echo "<p style='text-align:center; color:#7f8c8d;'>Boîte vide. 📭</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
