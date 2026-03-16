<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// --- RÉCUPÉRATION DES DONNÉES ---
// On trie par ID DESC pour avoir TOUJOURS les derniers tests en premier
$sql = "SELECT id, date_tes, debit_mbps FROM tests_debit ORDER BY id DESC LIMIT 10";
$history = $pdo->query($sql)->fetchAll();

// Préparation Graphique (on remet dans l'ordre pour que la courbe aille de gauche à droite)
$chart_data = array_reverse($history);
$labels = []; $values = [];
foreach ($chart_data as $row) {
    $labels[] = date('H:i', strtotime($row['date_tes']));
    $values[] = $row['debit_mbps'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Débit</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .speed-value { font-size: 3.5rem; font-weight: 800; color: #2563eb; }
        .progress-container { width: 100%; background: #eee; height: 12px; border-radius: 10px; margin: 20px 0; overflow: hidden; }
        #progress-bar { width: 0%; height: 100%; background: #2563eb; transition: width 0.1s linear; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Surveillance Flux & Débit</h1>

        <div class="card" style="text-align: center; padding: 30px;">
            <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem;">Mo/s</span></div>
            <p id="test-status">Prêt pour le test</p>
            <div class="progress-container"><div id="progress-bar"></div></div>
            <button id="start-btn" onclick="runSpeedTest()" class="btn-blue">
                <i class="fas fa-play"></i> LANCER LE TEST
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="card">
                <h3>📈 Performance réseau</h3>
                <div style="height: 250px;"><canvas id="debitChart"></canvas></div>
            </div>
            <div class="card">
                <h3>📂 Historique SQL</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($history as $h): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0;"><?= $h['date_tes'] ?></td>
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
    status.innerText = "Téléchargement...";
    
    const startTime = performance.now();
    try {
        // On utilise le chemin direct pour la VM
        const response = await fetch('generate_test_file.php?t=' + Date.now());
        const reader = response.body.getReader();
        let received = 0;
        const total = 10 * 1024 * 1024;

        while(true) {
            const {done, value} = await reader.read();
            if (done) break;
            received += value.length;
            bar.style.width = (received / total * 100) + "%";
        }

        const duration = (performance.now() - startTime) / 1000;
        const speed = (received / duration / 1024 / 1024).toFixed(2);

        display.innerHTML = `${speed} <span style="font-size: 1.2rem;">Mo/s</span>`;
        status.innerText = "Sauvegarde...";

        // Envoi des données
        await fetch('save_debit.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `debit=${speed}&temps=${duration.toFixed(2)}&taille=10`
        });

        // RECHARGEMENT FORCÉ : On attend un peu et on recharge la page
        setTimeout(() => { 
            window.location.reload(); 
        }, 800);

    } catch (e) { 
        status.innerText = "Erreur de connexion"; 
        btn.disabled = false;
    }
}

// Graphique
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
