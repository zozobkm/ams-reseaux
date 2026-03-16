<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- 1. GESTION DES MOTS-CLÉS (Image 5) ---
if ($is_avance && isset($_POST['add_keyword'])) {
    $word = trim($_POST['keyword']);
    if (!empty($word)) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO contenu_bloque (mot_cle) VALUES (?)");
        $stmt->execute([$word]);
    }
}
if ($is_avance && isset($_GET['del_kw'])) {
    $pdo->prepare("DELETE FROM contenu_bloque WHERE id = ?")->execute([$_GET['del_kw']]);
}

// --- 2. GESTION DES SERVICES (Ping / Accès Web) ---
if ($is_avance && isset($_POST['toggle_service'])) {
    $stmt = $pdo->prepare("UPDATE config_securite SET est_actif = !est_actif WHERE service_name = ?");
    $stmt->execute([$_POST['service_name']]);
}

// Récupération des données
$keywords = $pdo->query("SELECT * FROM contenu_bloque")->fetchAll();
$services = $pdo->query("SELECT * FROM config_securite")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Sécurité & Contrôle</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Administration Sécurité (Tâche S6)</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <div class="card">
                [cite_start]<h3><i class="fas fa-filter"></i> Contenu Bloqué [cite: 148]</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 15px;">
                    [cite_start]Bloquer l'accès aux sites contenant ces mots-clés[cite: 152].
                </p>

                <?php if ($is_avance): ?>
                <form method="post" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" name="keyword" placeholder="ex: Facebook, Poker..." style="flex: 1; padding: 8px; border-radius: 6px; border: 1px solid #ddd;">
                    [cite_start]<button type="submit" name="add_keyword" class="btn-blue">AJOUTER [cite: 155]</button>
                </form>
                <?php endif; ?>

                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($keywords as $kw): ?>
                    <span style="background: #f1f5f9; padding: 5px 12px; border-radius: 20px; font-size: 0.9rem; display: flex; align-items: center;">
                        <?= htmlspecialchars($kw['mot_cle']) ?>
                        <?php if ($is_avance): ?>
                        <a href="?del_kw=<?= $kw['id'] ?>" style="margin-left: 8px; color: #ef4444;"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-shield-alt"></i> Filtrage Services</h3>
                <div style="margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                        [cite_start]<span>Réponse au PING [cite: 108]</span>
                        <form method="post">
                            <input type="hidden" name="service_name" value="ping">
                            <button type="submit" name="toggle_service" class="<?= $services['ping'] ? 'btn-blue' : 'btn-blue' ?>" style="background: <?= $services['ping'] ? '#10b981' : '#ef4444' ?>; padding: 5px 15px;">
                                <?= $services['ping'] ? 'ACTIF' : 'BLOQUÉ' ?>
                            </button>
                        </form>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                        [cite_start]<span>Accès Web Extérieur [cite: 99]</span>
                        <form method="post">
                            <input type="hidden" name="service_name" value="web_externe">
                            <button type="submit" name="toggle_service" class="btn-blue" style="background: <?= $services['web_externe'] ? '#10b981' : '#ef4444' ?>; padding: 5px 15px;">
                                <?= $services['web_externe'] ? 'ACTIF' : 'BLOQUÉ' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card" style="grid-column: span 2;">
                [cite_start]<h3><i class="fas fa-clock"></i> Plages horaires d'accès [cite: 114]</h3>
                [cite_start]<p style="font-size: 0.85rem; color: #64748b;">Désactiver l'accès internet sur certaines périodes[cite: 112].</p>
                
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                    <tr style="background: #f8fafc; text-align: left;">
                        <th style="padding: 10px;">Jour</th>
                        <th style="padding: 10px;">00h - 08h</th>
                        <th style="padding: 10px;">08h - 18h</th>
                        <th style="padding: 10px;">18h - 00h</th>
                    </tr>
                    <?php 
                    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                    foreach($jours as $j): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; font-weight: bold;"><?= $j ?></td>
                        [cite_start]<td style="padding: 10px;"><span style="color: #ef4444;"><i class="fas fa-lock"></i> Bloqué [cite: 112]</span></td>
                        <td style="padding: 10px;"><span style="color: #10b981;"><i class="fas fa-check-circle"></i> Autorisé</span></td>
                        <td style="padding: 10px;"><span style="color: #10b981;"><i class="fas fa-check-circle"></i> Autorisé</span></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
