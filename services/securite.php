<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- BASCULER UNE HEURE PRÉCISE (Modularité totale) ---
if ($is_avance && isset($_POST['toggle_hour'])) {
    $stmt = $pdo->prepare("UPDATE planning_acces SET statut = IF(statut='autorise', 'bloque', 'autorise') WHERE id = ?");
    $stmt->execute([$_POST['slot_id']]);
}

// --- GESTION DES MOTS-CLÉS ---
if ($is_avance && isset($_POST['add_keyword'])) {
    $word = trim($_POST['keyword']);
    if (!empty($word)) {
        $pdo->prepare("INSERT IGNORE INTO contenu_bloque (mot_cle) VALUES (?)")->execute([$word]);
    }
}
if ($is_avance && isset($_GET['del_kw'])) {
    $pdo->prepare("DELETE FROM contenu_bloque WHERE id = ?")->execute([$_GET['del_kw']]);
}

// --- RÉCUPÉRATION ---
$keywords = $pdo->query("SELECT * FROM contenu_bloque")->fetchAll();
$services = $pdo->query("SELECT * FROM config_securite")->fetchAll(PDO::FETCH_KEY_PAIR);
$planning_raw = $pdo->query("SELECT * FROM planning_acces ORDER BY FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), heure")->fetchAll();

// On réorganise les données pour l'affichage en grille
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
        .hour-grid { display: flex; gap: 2px; background: #eee; padding: 2px; border-radius: 4px; overflow-x: auto; }
        .hour-btn { 
            flex: 1; min-width: 30px; height: 40px; border: none; font-size: 0.7rem; 
            cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .h-autorise { background: #10b981; color: white; }
        .h-bloque { background: #ef4444; color: white; }
        .h-autorise:hover { background: #059669; }
        .h-bloque:hover { background: #dc2626; }
        .day-label { width: 80px; font-weight: bold; font-size: 0.9rem; }
        .planning-row { display: flex; align-items: center; margin-bottom: 8px; gap: 10px; }
        .badge-kw { background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; display: flex; align-items: center; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Contrôle Parental & Sécurité</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="card">
                <h3><i class="fas fa-filter"></i> Filtrage de Contenu</h3>
                <?php if ($is_avance): ?>
                <form method="post" style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" name="keyword" placeholder="ex: Facebook" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    <button type="submit" name="add_keyword" class="btn-blue">AJOUTER</button>
                </form>
                <?php endif; ?>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach ($keywords as $kw): ?>
                    <span class="badge-kw">
                        <?= htmlspecialchars($kw['mot_cle']) ?>
                        <?php if ($is_avance): ?>
                        <a href="?del_kw=<?= $kw['id'] ?>" style="margin-left:8px; color:#dc2626;"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-shield-alt"></i> Services Réseau</h3>
                <?php foreach ($services as $name => $active): ?>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <span><?= strtoupper(str_replace('_', ' ', $name)) ?></span>
                    <span style="color: <?= $active ? '#10b981' : '#ef4444' ?>; font-weight: bold;">
                        <?= $active ? 'ACTIF' : 'BLOQUÉ' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-clock"></i> Plages Horaires d'Accès Internet (00h - 23h)</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">
                Chaque carré représente 1 heure. Cliquez pour changer l'état (Vert = OK, Rouge = Bloqué).
            </p>

            <div style="margin-bottom: 10px; display: flex; padding-left: 90px; font-size: 0.7rem; color: #94a3b8;">
                <?php for($i=0; $i<24; $i++) echo "<div style='flex:1; text-align:center;'>".$i."h</div>"; ?>
            </div>

            <?php foreach($planning as $day => $hours): ?>
            <div class="planning-row">
                <div class="day-label"><?= $day ?></div>
                <div class="hour-grid">
                    <?php foreach($hours as $h): ?>
                    <form method="post" style="flex: 1; margin: 0;">
                        <input type="hidden" name="slot_id" value="<?= $h['id'] ?>">
                        <button type="submit" name="toggle_hour" 
                                class="hour-btn <?= $h['statut'] == 'autorise' ? 'h-autorise' : 'h-bloque' ?>"
                                title="<?= $h['heure'] ?>h : <?= $h['statut'] ?>"
                                <?= !$is_avance ? 'disabled' : '' ?>>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; display: flex; gap: 20px; font-size: 0.8rem; justify-content: center;">
                <span><i class="fas fa-square" style="color: #10b981;"></i> Accès Autorisé</span>
                <span><i class="fas fa-square" style="color: #ef4444;"></i> Accès Bloqué</span>
            </div>
        </div>
    </div>
</body>
</html>
