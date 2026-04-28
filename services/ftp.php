<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// --- GÉNÉRATION DU FLUX ---
if (isset($_GET['action']) && $_GET['action'] === 'generate') {
    if (ob_get_level()) ob_end_clean();
    header("Content-Type: application/octet-stream");
    header("Content-Length: 10485760"); 
    for ($i = 0; $i < 160; $i++) {
        echo str_repeat("0", 65536); 
        flush();
    }
    exit;
}

// ---  SAUVEGARDE SQL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debit'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO tests_debit (temps_sec, taille_mo, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['temps'], $_POST['taille'], $_POST['debit']]);
        echo "OK";
    } catch (PDOException $e) { echo "Erreur SQL"; }
    exit; 
}

// ---  RÉCUPÉRATION DONNÉES ---
$history = $pdo->query("SELECT * FROM tests_debit ORDER BY id DESC LIMIT 10")->fetchAll();
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
    <title>CeriBox - Débit Réseau</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="../assets/chart.min.js"></script>

    <style>
        .speed-value { font-size: 3.5rem; font-weight: 800; color: #2563eb; margin: 10px 0; }
        .progress-container { width: 100%; background: #edf2f7; height: 12px; border-radius: 10px; margin: 20px 0; overflow: hidden; }
        #progress-bar { width: 0%; height: 100%; background: #2563eb; transition: width 0.1s linear; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h1>Surveillance Flux & Débit</h1>
        
        <div class="card" style="text-align: center; padding: 40px;">
            <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem;">Mo/s</span></div>
            <p id="test-status">Prêt pour le test (10 Mo)</p>
            <div class="progress-container"><div id="progress-bar"></div></div>
            <button id="start-btn" onclick="runSpeedTest()" class="btn-blue">LANCER LE TEST</button>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="card">
                <h3>Performance réseau</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="debitChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h3> Historique SQL</h3>
                <table style="width: 100%; font-size: 0.9rem;">
                    <?php foreach ($history as $h): ?>
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
function runSpeedTest() {
    const btn = document.getElementById('start-btn');
    const display = document.getElementById('speed-display');
    const bar = document.getElementById('progress-bar');
    const status = document.getElementById('test-status');

    btn.disabled = true;
    status.innerText = "Téléchargement...";
    
    const startTime = new Date().getTime();
    const xhr = new XMLHttpRequest();
    const url = 'ftp.php?action=generate&t=' + new Date().getTime();

    xhr.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            bar.style.width = percent + "%";
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            const endTime = new Date().getTime();
            const duration = (endTime - startTime) / 1000;
            const sizeMo = (xhr.response.byteLength / 1024 / 1024).toFixed(2);
            const speed = (sizeMo / duration).toFixed(2);

            display.innerHTML = speed + ' <span style="font-size: 1.2rem;">Mo/s</span>';
            status.innerText = "Sauvegarde...";

            const saveXhr = new XMLHttpRequest();
            saveXhr.open('POST', 'ftp.php', true);
            saveXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            saveXhr.onload = function() { location.reload(); };
            saveXhr.send('debit=' + speed + '&temps=' + duration.toFixed(2) + '&taille=' + sizeMo);
        }
    };

    xhr.open('GET', url, true);
    xhr.responseType = 'arraybuffer';
    xhr.send();
}

// Initialisation sécurisée du graphique
document.addEventListener("DOMContentLoaded", function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('debitChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Mo/s',
                    data: <?= json_encode($values) ?>,
                    borderColor: '#2563eb',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    } else {
        console.error("Chart.js n'est pas chargé. Vérifiez le chemin : ../assets/chart.min.js");
    }
});
</script>
</body>
</html>
