<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- LOGIQUE : BASCULER UNE HEURE ---
if ($is_avance && isset($_POST['toggle_hour'])) {
    $stmt = $pdo->prepare("UPDATE planning_acces SET statut = IF(statut='autorise', 'bloque', 'autorise') WHERE id = ?");
    $stmt->execute([$_POST['slot_id']]);
}

// --- LOGIQUE : MOTS-CLÉS ---
if ($is_avance && isset($_POST['add_keyword'])) {
    $word = trim($_POST['keyword']);
    if (!empty($word)) {
        $pdo->prepare("INSERT IGNORE INTO contenu_bloque (mot_cle) VALUES (?)")->execute([$word]);
    }
}
if ($is_avance && isset($_GET['del_kw'])) {
    $pdo->prepare("DELETE FROM contenu_bloque WHERE id = ?")->execute([$_GET['del_kw']]);
}

// Récupération
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
        
        /* Grille de 24 colonnes identiques */
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Contrôle Parental & Sécurité</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div class="card">
                <h3><i class="fas fa-filter"></i> Filtrage de Contenu</h3>
                <?php if ($is_avance): ?>
                <form method="post" style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" name="keyword" placeholder="Bloquer un mot (ex: Poker)" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                    <button type="submit" name="add_keyword" class="btn-blue">AJOUTER</button>
                </form>
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
                    <div class="day-label"><?= $day ?></div>
                    <div class="hour-grid">
                        <?php foreach($hours as $h): ?>
                        <form method="post" style="margin: 0; padding: 0;">
                            <input type="hidden" name="slot_id" value="<?= $h['id'] ?>">
                            <button type="submit" name="toggle_hour" 
                                    class="hour-btn <?= $h['statut'] == 'autorise' ? 'h-autorise' : 'h-bloque' ?>"
                                    title="<?= $h['heure'] ?>h - <?= $h['statut'] ?>"
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
