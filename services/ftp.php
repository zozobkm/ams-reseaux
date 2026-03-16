<?php
require_once __DIR__ . "/../auth/require_login.php";
require_once 'db.php';

// --- 1. ACTION : GÉNÉRATION DU FLUX (Test de téléchargement) ---
if (isset($_GET['action']) && $_GET['action'] === 'generate') {
    if (ob_get_level()) ob_end_clean();
    header("Content-Type: application/octet-stream");
    header("Content-Length: 10485760"); // 10 Mo exacts
    for ($i = 0; $i < 160; $i++) {
        echo str_repeat("0", 65536); 
        flush();
    }
    exit;
}

// --- 2. ACTION : SAUVEGARDE SQL (Réception des résultats) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debit'])) {
    try {
        // Insertion dans ta table tests_debit
        $stmt = $pdo->prepare("INSERT INTO tests_debit (temps_sec, taille_mo, debit_mbps) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['temps'], 
            $_POST['taille'], 
            $_POST['debit']
        ]);
        echo "Succès";
    } catch (PDOException $e) { echo "Erreur SQL"; }
    exit; 
}

// --- 3. PRÉPARATION DE L'AFFICHAGE ---
$mode = $_SESSION["mode"] ?? "normal";
$is_avance = ($mode === "avance");

// On récupère les 10 derniers tests (Tri par ID pour avoir les plus récents en haut)
$history = $pdo->query("SELECT * FROM tests_debit ORDER BY id DESC LIMIT 10")->fetchAll();

// On inverse pour le graphique (ordre chronologique de gauche à droite)
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div id="speed-display" class="speed-value">0.00 <span style="font-size: 1.2rem; color: #64748b;">Mo/s</span></div>
            <p id="test-status" style="color: #64748b;">Prêt pour la mesure réelle</p>
            <div class="progress-container"><div id="progress-bar"></div></div>
            <button id="start-btn" onclick="runSpeedTest()" class="btn-blue" style="padding: 15px 50px; border-radius: 50px;">
                <i class="fas fa-play"></i> LANCER LE TEST
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="card">
                <h3>📈 Performance réseau (Graphique)</h3>
                <div style="height: 250px; margin-top:20px;"><canvas id="debitChart"></canvas></div>
            </div>
            <div class="card">
                <h3>📂 Historique SQL (Derniers tests)</h3>
                <table style="width: 100%; font-size: 0.9rem; margin-top: 15px;">
                    <?php foreach ($history as $h): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px 0; color: #64748b;"><?= $h['date_tes'] ?></td>
                        <td style="padding: 8px 0; text-align: right; font-weight: bold;"><?= $h['debit_mbps'] ?> Mo/s</td>
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
    status.innerText = "Téléchargement du flux de test...";
    
    const startTime = performance.now();
    try {
        // On s'appelle soi-même pour le flux
        const response = await fetch('ftp.php?action=generate&t=' + Date.now());
        if (!response.ok) throw new Error("Erreur serveur");

        const reader = response.body.getReader();
        let received = 0;
        const total = 10485760; // 10 Mo

        while(true) {
            const {done, value} = await reader.read();
            if (done) break;
            received += value.length;
            bar.style.width = Math.min((received / total * 100), 100) + "%";
        }

        const duration = (performance.now() - startTime) / 1000;
        const sizeMo = (received / 1024 / 1024).toFixed(2);
        const speed = (sizeMo / duration).toFixed(2);

        display.innerHTML = speed + ' <span style="font-size: 1.2rem; color: #64748b;">Mo/s</span>';
        status.innerText = "Mise à jour de la base de données...";

        // Envoi POST au même fichier pour sauvegarde
        await fetch('ftp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `debit=${speed}&temps=${duration.toFixed(2)}&taille=${sizeMo}`
        });

        // Actualisation automatique pour voir le résultat dans le tableau et le graph
        setTimeout(() => { location.reload(); }, 1000);

    } catch (e) { 
        status.innerText = "Erreur : " + e.message; 
        btn.disabled = false;
    }
}

// Graphique (s'affiche si Chart.js est chargé via le NAT/Internet)
if (window.Chart) {
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
}
</script>
</body>
</html>
