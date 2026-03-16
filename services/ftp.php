<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// --- LOGIQUE EXPERT ---
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

if (isset($_POST['clear_db']) && $is_avance) {
    $pdo->exec("DELETE FROM tests_debit");
    header("Location: ftp.php");
    exit;
}

// --- RÉCUPÉRATION DES DONNÉES (Ordre DESC pour le tableau, ASC pour le graph) ---
try {
    // 1. Pour le tableau (Les plus récents en haut)
    $sql_tab = "SELECT date_tes, debit_mbps FROM tests_debit ORDER BY date_tes DESC LIMIT 10";
    $history = $pdo->query($sql_tab)->fetchAll();

    // 2. Pour le graphique (Ordre chronologique)
    $chart_data = array_reverse($history);
    $labels = [];
    $values = [];
    foreach ($chart_data as $row) {
        $labels[] = date('H:i', strtotime($row['date_tes']));
        $values[] = $row['debit_mbps'];
    }
} catch (Exception $e) { $error = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Surveillance Débit</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .speed-value { font-size: 3.5rem; font-weight: 800; color: var(--primary); margin: 10px 0; }
        .progress-container { width: 100%; background: #edf2f7; height: 12px; border-radius: 10px; margin: 25px 0; overflow: hidden; }
        #progress-bar { width: 0%; height: 100%; background: var(--primary); transition: width 0.1s linear; }
        .stats-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>Surveillance Flux & Débit</h1>
        </div>

        <div class="card" style="text-align: center; padding: 40px;">
            <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem; color: var(--text-muted);">Mo/s</span></div>
            <p id="test-status" style="color: var(--text-muted);">Prêt pour le test (10 Mo)</p>
            <div class="progress-container"><div id="progress-bar"></div></div>
            <button id="start-btn" onclick="runSpeedTest()" class="btn-blue" style="padding: 15px 50px; border-radius: 50px;">
                <i class="fas fa-play"></i> LANCER LE TEST
            </button>
        </div>

        <div class="stats-grid">
            <div class="card">
                <h3><i class="fas fa-chart-line"></i> Performance réseau</h3>
                <div style="height: 250px; margin-top:20px;"><canvas id="debitChart"></canvas></div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3><i class="fas fa-history"></i> Historique SQL</h3>
                    <?php if ($is_avance): ?>
                        <form method="post">
                            <button type="submit" name="clear_db" class="btn-blue" style="background: #ef4444; padding: 5px 10px; font-size: 0.8rem;">Vider</button>
                        </form>
                    <?php endif; ?>
                </div>
                <table style="width: 100%; font-size: 0.9rem;">
                    <?php foreach ($history as $h): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 0; color: var(--text-muted);"><?= $h['date_tes'] ?></td>
                        <td style="padding: 8px 0; text-align: right; font-weight: bold;"><?= $h['debit_mbps'] ?> Mo/s</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

<script>
// --- LE COEUR DU TEST ---
async function runSpeedTest() {
    const btn = document.getElementById('start-btn');
    const display = document.getElementById('speed-display');
    const bar = document.getElementById('progress-bar');
    const status = document.getElementById('test-status');

    btn.disabled = true;
    status.innerText = "Calcul du débit en cours...";
    
    const startTime = new Date().getTime();
    try {
        // SOLUTION IP FIXE : On utilise un chemin relatif './' pour que le navigateur ne se perde pas
        const response = await fetch('./generate_test_file.php');
        if (!response.ok) throw new Error();

        const reader = response.body.getReader();
        let received = 0;
        const total = 10 * 1024 * 1024; // 10 Mo

        while(true) {
            const {done, value} = await reader.read();
            if (done) break;
            received += value.length;
            bar.style.width = (received / total * 100) + "%";
        }

        const duration = (new Date().getTime() - startTime) / 1000;
        const sizeMo = (received / 1024 / 1024).toFixed(2);
        const speed = (sizeMo / duration).toFixed(2);

        display.innerHTML = `${speed} <span style="font-size: 1.2rem;">Mo/s</span>`;
        status.innerText = "Mise à jour du graphique...";

        // SAUVEGARDE SQL (Tâche S5/S6)
        await fetch('./save_debit.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `debit=${speed}&temps=${duration}&taille=${sizeMo}`
        });

        // RECHARGEMENT POUR VOIR LES NOUVELLES DONNÉES DANS LE GRAPH/TABLEAU
        setTimeout(() => { window.location.href = "ftp.php"; }, 1000);

    } catch (e) { 
        status.innerText = "Erreur : Impossible de joindre la Box sur " + window.location.hostname;
        btn.disabled = false; 
    }
}

// --- GRAPHIQUE ---
const ctx = document.getElementById('debitChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Débit (Mo/s)',
            data: <?= json_encode($values) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
</body>
</html>
