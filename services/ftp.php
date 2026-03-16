<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// 1. Préparation des données pour le graphique (Tâche S6)
$labels = [];
$values = [];
try {
    // On récupère les 10 derniers tests pour la courbe
    $sql_graph = "SELECT date_mesure, debit FROM historique_debit 
                  WHERE user_id = ? ORDER BY date_mesure ASC LIMIT 10";
    $stmt = $pdo->prepare($sql_graph);
    $stmt->execute([$_SESSION['user_id']]);
    $history = $stmt->fetchAll();

    foreach ($history as $row) {
        $labels[] = date('H:i', strtotime($row['date_mesure']));
        $values[] = $row['debit'];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");
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
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../menu.php'; ?>

<div class="main-content">
    <div class="header-page">
        <h1>Surveillance Flux & Débit</h1>
        <span class="status-badge-mini" style="background: #10b981; color:white;">SYSTÈME NOMINAL</span>
    </div>

    <div class="card" style="text-align: center; padding: 40px;">
        <i class="fas fa-gauge-high" style="font-size: 2.5rem; color: var(--primary); opacity: 0.2;"></i>
        <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem; color: var(--text-muted);">Mo/s</span></div>
        <p id="test-status" style="color: var(--text-muted); font-weight: 500;">Prêt pour la mesure réelle</p>
        
        <div class="progress-container">
            <div id="progress-bar"></div>
        </div>

        <button id="start-btn" onclick="runSpeedTest()" class="btn-blue" style="padding: 15px 50px; font-size: 1.1rem; border-radius: 50px;">
            <i class="fas fa-bolt"></i> DÉMARRER LE TEST
        </button>
    </div>

    <div class="stats-grid">
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Évolution des performances</h3>
            <div style="height: 250px; margin-top:20px;">
                <canvas id="debitChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Historique SQL</h3>
            <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                <?php foreach (array_reverse($history) as $h): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; color: var(--text-muted);"><?= $h['date_mesure'] ?></td>
                    <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= $h['debit'] ?> Mo/s</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<script>
// --- LOGIQUE DU SPEEDTEST (JS ASYNC) ---
async function runSpeedTest() {
    const btn = document.getElementById('start-btn');
    const display = document.getElementById('speed-display');
    const status = document.getElementById('test-status');
    const bar = document.getElementById('progress-bar');

    btn.disabled = true;
    btn.style.opacity = "0.5";
    status.innerText = "Téléchargement du flux de test (10 Mo)...";
    
    const startTime = new Date().getTime();
    try {
        // On appelle le générateur de flux
        const response = await fetch('generate_test_file.php');
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
        const speed = (received / duration / 1024 / 1024).toFixed(2);

        display.innerHTML = `${speed} <span style="font-size: 1.2rem; color: var(--text-muted);">Mo/s</span>`;
        status.innerText = "Test réussi ! Enregistrement...";

        // Sauvegarde AJAX en base (Tâche S6)
        await fetch('save_debit.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `debit=${speed}`
        });

        setTimeout(() => location.reload(), 1500);

    } catch (e) {
        status.innerText = "Erreur : La Box ne répond pas.";
        btn.disabled = false;
    }
}

// --- CONFIGURATION DU GRAPHIQUE CHART.JS ---
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
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
