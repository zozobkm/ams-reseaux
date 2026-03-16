<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// Extraction des données pour le graphique (Tâche S6)
$labels = [];
$values = [];
try {
    // On utilise tes colonnes : date_tes et debit_mbps
    $sql_graph = "SELECT date_tes, debit_mbps FROM tests_debit ORDER BY date_tes ASC LIMIT 10";
    $stmt = $pdo->query($sql_graph);
    $history = $stmt->fetchAll();

    foreach ($history as $row) {
        $labels[] = date('H:i', strtotime($row['date_tes']));
        $values[] = $row['debit_mbps'];
    }
} catch (Exception $e) { $error = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Débit Réseau</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .speed-value { font-size: 3.5rem; font-weight: 800; color: var(--primary); margin: 10px 0; }
        .progress-container { width: 100%; background: #edf2f7; height: 12px; border-radius: 10px; margin: 25px 0; overflow: hidden; }
        #progress-bar { width: 0%; height: 100%; background: var(--primary); transition: width 0.1s linear; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Surveillance Flux & Débit</h1>

        <div class="card" style="text-align: center; padding: 40px;">
            <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem; color: var(--text-muted);">Mo/s</span></div>
            <p id="test-status" style="color: var(--text-muted);">Prêt pour le test réel (10 Mo)</p>
            
            <div class="progress-container"><div id="progress-bar"></div></div>

            <button id="start-btn" onclick="runSpeedTest()" class="btn-blue" style="padding: 15px 50px; border-radius: 50px;">
                <i class="fas fa-play"></i> LANCER LE TEST
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="card">
                <h3>📈 Graphique de performance</h3>
                <div style="height: 250px;"><canvas id="debitChart"></canvas></div>
            </div>
            <div class="card">
                <h3>📂 Historique (Table tests_debit)</h3>
                <table style="width: 100%; font-size: 0.9rem; margin-top: 10px;">
                    <?php foreach (array_reverse($history) as $h): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px 0;"><?= $h['date_tes'] ?></td>
                        <td style="text-align: right; font-weight: bold;"><?= $h['debit_mbps'] ?> Mo/s</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

<script>
async function runSpeedTest() {
    const btn = document.getElementById('start-btn');
    const display = document.getElementById('speed-display');
    const bar = document.getElementById('progress-bar');
    const status = document.getElementById('test-status');

    btn.disabled = true;
    status.innerText = "Téléchargement en cours...";
    
    const startTime = new Date().getTime();
    try {
        const response = await fetch('generate_test_file.php');
        const reader = response.body.getReader();
        let received = 0;
        const total = 10 * 1024 * 1024; // 10Mo

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
        status.innerText = "Enregistrement SQL...";

        // Envoi vers ta table tests_debit
        await fetch('save_debit.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `debit=${speed}&temps=${duration}&taille=${sizeMo}`
        });

        setTimeout(() => location.reload(), 1200);
    } catch (e) { status.innerText = "Erreur de connexion."; btn.disabled = false; }
}

const ctx = document.getElementById('debitChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Débit Mo/s',
            data: <?= json_encode($values) ?>,
            borderColor: '#2563eb',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(37, 99, 235, 0.1)'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
</body>
</html>
