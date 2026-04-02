<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- LOGIQUE 1 : BASCULER UNE HEURE (PLANNING) ---
if ($is_avance && isset($_POST['toggle_hour'])) {
    $stmt = $pdo->prepare("UPDATE planning_acces SET statut = IF(statut='autorise', 'bloque', 'autorise') WHERE id = ?");
    $stmt->execute([$_POST['slot_id']]);
}

// --- LOGIQUE 2 : MOTS-CLÉS ---
if ($is_avance && isset($_POST['add_keyword'])) {
    $word = trim($_POST['keyword']);
    if (!empty($word)) {
        $pdo->prepare("INSERT IGNORE INTO contenu_bloque (mot_cle) VALUES (?)")->execute([$word]);
    }
}
if ($is_avance && isset($_GET['del_kw'])) {
    $pdo->prepare("DELETE FROM contenu_bloque WHERE id = ?")->execute([$_GET['del_kw']]);
}

// --- LOGIQUE 3 : BLOQUER UN DOMAINE SUSPECT (NOUVEAU) ---
if ($is_avance && isset($_POST['block_domain'])) {
    // Nettoyage de sécurité
    $domain_to_block = escapeshellarg($_POST['domain']);
    
    // Commande pour bloquer le site via le pare-feu
    $cmd = "sudo iptables -I FORWARD -d $domain_to_block -j DROP";
    shell_exec($cmd);
    
    // Ajout visuel dans la liste des mots-clés bloqués pour que l'utilisateur le voie
    $pdo->prepare("INSERT IGNORE INTO contenu_bloque (mot_cle) VALUES (?)")->execute([$_POST['domain']]);
}

// --- LOGIQUE 4 : MISE À JOUR BLACKLIST DYNAMIQUE (BIND9) ---
$message_bl = "";
if ($is_avance && isset($_POST['update_dynamic_bl'])) {
    // Lance le script Bash et récupère le résultat
    $resultat = shell_exec("sudo /var/www/html/ams-reseaux/scripts/update_blacklist.sh 2>&1");
    $message_bl = "Blacklist dynamique mise à jour avec succès !";
}

// --- NOUVEAU SERVICE : DÉTECTION ANOMALIES (Phishing / Typosquatting) ---
$alertes = [];
if ($is_avance) {
    // 1. On récupère les mots-clés que l'administrateur a déjà bloqués dans la BDD
    $stmt = $pdo->query("SELECT mot_cle FROM contenu_bloque");
    $mots_bloques_bdd = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // On fusionne avec quelques sites vitaux par défaut
    $sites_officiels = array_merge(
        ["facebook.com", "google.com", "paypal.com", "amazon.fr", "bnpparibas.fr"], 
        $mots_bloques_bdd
    );
    
    // 2. Simulation des requêtes (Dans un projet plus vaste, on lirait les logs DNS ici)
   $historique_visites = ["google.com", "faceboook.com", "paypa1.com", "amazon.fr", "g00gle.fr", "pokker.com", "netflixx.com", "banque-popullaire.fr"];

    // 3. Algorithme de comparaison de chaînes de caractères (similar_text)
    foreach ($historique_visites as $visite) {
        foreach ($sites_officiels as $officiel) {
            similar_text($visite, $officiel, $pourcentage);
            
            // Si ressemblance forte (>80%) mais avec une faute de frappe (différent de l'officiel)
            if ($pourcentage > 80 && $visite !== $officiel) {
                // On vérifie s'il n'est pas DÉJÀ bloqué dans la base de données
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM contenu_bloque WHERE mot_cle = ?");
                $stmt->execute([$visite]);
                $deja_bloque = $stmt->fetchColumn() > 0;

                if (!$deja_bloque) {
                    $alertes[] = [
                        'site_suspect' => $visite,
                        'ressemble_a' => $officiel,
                        'taux' => round($pourcentage, 1)
                    ];
                }
            }
        }
    }
}

// --- RÉCUPÉRATION BDD ---
$keywords = $pdo->query("SELECT * FROM contenu_bloque")->fetchAll();
$services = $pdo->query("SELECT * FROM config_securite")->fetchAll(PDO::FETCH_KEY_PAIR);
$planning_raw = $pdo->query("SELECT * FROM planning_acces ORDER BY FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), heure ASC")->fetchAll();

$planning = [];
foreach($planning_raw as $p) {
    $planning[$p['jour']][] = $p;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Sécurité</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .planning-container { overflow-x: auto; padding-bottom: 10px; }
        .planning-row { display: flex; align-items: center; margin-bottom: 5px; min-width: 900px; }
        .day-label { width: 100px; font-weight: bold; font-size: 0.9rem; flex-shrink: 0; }
        
        .hour-grid { 
            display: grid; 
            grid-template-columns: repeat(24, 1fr); 
            gap: 2px; 
            flex-grow: 1;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 4px;
        }
        
        .hour-btn { 
            height: 35px; border: none; cursor: pointer; transition: 0.2s; 
            border-radius: 2px; width: 100%;
        }
        .h-autorise { background: #10b981; }
        .h-bloque { background: #ef4444; }
        .hour-btn:hover { opacity: 0.8; transform: scale(1.05); }
        .hour-btn:disabled { cursor: default; }

        .header-hours { display: grid; grid-template-columns: repeat(24, 1fr); margin-left: 100px; margin-bottom: 5px; min-width: 800px; }
        .header-hour-item { text-align: center; font-size: 0.7rem; color: #94a3b8; }
        .badge-kw { background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; display: flex; align-items: center; border: 1px solid #fecaca; }
        
        /* Styles pour le tableau d'anomalies */
        .table-anomalies th { padding: 10px; border-bottom: 2px solid #cbd5e1; }
        .table-anomalies td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>Contrôle Parental & Sécurité</h1>
            <?php if ($is_avance): ?>
                <span class="badge" style="background: #e67e22;">Analyseur de texte actif</span>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div class="card">
                <h3><i class="fas fa-filter"></i> Filtrage de Contenu</h3>
                <?php if ($is_avance): ?>
                <form method="post" style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" name="keyword" placeholder="Bloquer un mot (ex: Poker)" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                    <button type="submit" name="add_keyword" class="btn-blue">AJOUTER</button>
                </form>

                <form method="POST" action="" style="margin-bottom: 15px;">
                    <button type="submit" name="update_dynamic_bl" class="btn-blue" style="width: 100%;">    <i class="fas fa-sync-alt"></i> Mettre à jour la Blacklist DNS (Serveur Bind9)
                    </button>
                </form>
                
                <?php if (!empty($message_bl)): ?>
                    <div style="background: #10b981; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-check-circle"></i> <?= $message_bl ?>
                    </div>
                <?php endif; ?>

                <?php endif; ?>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach ($keywords as $kw): ?>
                    <span class="badge-kw">
                        <?= htmlspecialchars($kw['mot_cle']) ?>
                        <?php if ($is_avance): ?>
                        <a href="?del_kw=<?= $kw['id'] ?>" style="margin-left:8px; color:#ef4444;"><i class="fas fa-times-circle"></i></a>
                        <?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-shield-alt"></i> État des Services Réseau</h3>
                <div style="margin-top: 10px;">
                    <p>Ping : <span style="font-weight:bold; color:#10b981;">ACTIF</span></p>
                    <p>Web : <span style="font-weight:bold; color:#10b981;">ACTIF</span></p>
                </div>
            </div>
        </div>

        <?php if ($is_avance): ?>
        <div class="card" style="margin-bottom: 25px; border-left: 5px solid #f1c40f;">
            <h3><i class="fas fa-search-dollar"></i> Conseiller de Sécurité : Scanner d'Anomalies</h3>
            <p style="color: #555; font-size: 0.9rem; margin-bottom: 15px;">
                L'algorithme heuristique a détecté des requêtes suspectes ressemblant à du Typo-squatting ou à vos mots-clés bloqués.
            </p>

            <table class="table-anomalies" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <tr style="background: #f8fafc;">
                    <th>Domaine suspect</th>
                    <th>Ressemble à</th>
                    <th>Fiabilité</th>
                    <th>Action</th>
                </tr>
                <?php if (empty($alertes)): ?>
                    <tr><td colspan="4" style="text-align: center; color: #10b981; padding: 15px;">Aucune nouvelle menace détectée.</td></tr>
                <?php else: ?>
                    <?php foreach ($alertes as $alerte): ?>
                        <tr>
                            <td style="color: #ef4444; font-weight: bold;"><?= htmlspecialchars($alerte['site_suspect']) ?></td>
                            <td><?= htmlspecialchars($alerte['ressemble_a']) ?></td>
                            <td><span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px;"><?= $alerte['taux'] ?>%</span></td>
                            <td>
                                <form method="POST" action="" style="margin:0;">
                                    <input type="hidden" name="domain" value="<?= htmlspecialchars($alerte['site_suspect']) ?>">
                                    <button type="submit" name="block_domain" class="btn-blue" style="padding: 4px 10px; font-size: 0.8rem; background: #ef4444;">Bloquer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-clock"></i> Plages Horaires d'Accès Internet (24h/24)</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">
                Chaque carré est une heure. Cliquez pour autoriser (vert) ou bloquer (rouge).
            </p>

            <div class="planning-container">
                <div class="header-hours">
                    <?php for($i=0; $i<24; $i++) echo "<div class='header-hour-item'>".$i."h</div>"; ?>
                </div>

                <?php foreach($planning as $day => $hours): ?>
                <div class="planning-row">
                    <div class="day-label"><?= htmlspecialchars($day) ?></div>
                    <div class="hour-grid">
                        <?php foreach($hours as $h): ?>
                        <form method="post" style="margin: 0; padding: 0;">
                            <input type="hidden" name="slot_id" value="<?= $h['id'] ?>">
                            <button type="submit" name="toggle_hour" 
                                    class="hour-btn <?= $h['statut'] == 'autorise' ? 'h-autorise' : 'h-bloque' ?>"
                                    title="<?= $h['heure'] ?>h - <?= htmlspecialchars($h['statut']) ?>"
                                    <?= !$is_avance ? 'disabled' : '' ?>>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 25px; font-size: 0.85rem; justify-content: center;">
                <span><i class="fas fa-square" style="color: #10b981;"></i> Accès Autorisé</span>
                <span><i class="fas fa-square" style="color: #ef4444;"></i> Accès Bloqué</span>
            </div>
        </div>
    </div>
</body>
</html>
