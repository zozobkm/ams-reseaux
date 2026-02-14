<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Déclaration des variables
$resultat = "";
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// Si le formulaire est soumis pour configurer DNS
if (isset($_POST['configurer'])) {
    // Récupération et sécurisation du domaine DNS
    $domaine = escapeshellarg(trim($_POST['domaine']));
    
    // Exécution de la commande DNS via le script Bash
$cmd = "sudo /var/www/html/ams-reseaux/scripts/config_dns.sh $domaine";
    $resultat = shell_exec($cmd . " 2>&1");
    echo "<pre>$resultat</pre>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Configuration DNS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <?php if (file_exists(__DIR__ . '/../menu.php')) include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        
        <div class="header-page">
            <h1>Configuration DNS (Bind9)</h1>
            <span class="badge" style="background: <?= $is_avance ? '#e67e22' : '#3498db' ?>;">
                Mode <?= htmlspecialchars(ucfirst($mode)) ?>
            </span>
        </div>

        <div class="card">
            <h3>📖 Gestion des noms de domaine</h3>
            <p style="color: #555; margin-bottom: 20px;">
                Le service DNS permet de traduire les noms de domaine (ex: <em>ceribox.lan</em>) en adresses IP locales. 
                Cela facilite l'accès aux services sans avoir à retenir les numéros IP.
            </p>

            <form method="post" style="max-width: 500px;">
                <div style="margin-bottom: 15px;">
                    <label for="domaine" style="display: block; margin-bottom: 8px; font-weight: bold;">Nom de domaine à enregistrer :</label>
                    <input type="text" id="domaine" name="domaine" placeholder="Ex: mon-serveur.lan" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                </div>

                <button type="submit" name="configurer" class="btn-blue">
                    Appliquer la configuration
                </button>
            </form>
        </div>

        

        <?php if ($resultat !== ""): ?>
            <div class="card" style="background: #1e293b; color: #38bdf8; border: none;">
                <h4 style="color: #94a3b8; margin-top: 0;">📜 Logs système (Bind9) :</h4>
                <pre style="font-family: 'Courier New', monospace; white-space: pre-wrap; margin-bottom: 0;"><?= htmlspecialchars($resultat) ?></pre>
            </div>
        <?php endif; ?>

        <?php if ($is_avance): ?>
            <div class="card" style="border-left: 5px solid #e67e22;">
                <h3 style="color: #e67e22;">Informations techniques</h3>
                <p style="font-size: 0.9em; line-height: 1.5;">
                    La modification du domaine entraîne la mise à jour des fichiers de zone dans <code>/etc/bind/</code>. 
                    Le script redémarre automatiquement le service pour appliquer les changements.
                </p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
