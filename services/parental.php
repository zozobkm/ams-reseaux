<?php
session_start();
require_once __DIR__ . "/../auth/require_login.php";

// Restriction au mode Expert comme demandé
$is_avance = ($_SESSION["mode"] ?? "normal") === "avance";
if (!$is_avance) {
    header("Location: /ams-reseaux/dashboard/index.php");
    exit;
}

// 1. Liste des domaines "officiels" à protéger (Base de comparaison)
$sites_officiels = ["facebook.com", "google.com", "paypal.com", "amazon.fr", "bnpparibas.fr"];

// 2. Simulation de récupération des logs (ce que les clients visitent réellement)
// Dans un vrai cas, vous liriez ici un fichier de log DNS ou HTTP.
$historique_visites = ["google.com", "faceboook.com", "paypa1.com", "amazon.fr", "g00gle.fr"];

$alertes = [];

// 3. Algorithme de traitement de chaînes
foreach ($historique_visites as $visite) {
    foreach ($sites_officiels as $officiel) {
        similar_text($visite, $officiel, $pourcentage);
        
        // Si ressemblance forte (>80%) mais pas identique
        if ($pourcentage > 80 && $visite !== $officiel) {
            $alertes[] = [
                'site_suspect' => $visite,
                'ressemble_a' => $officiel,
                'taux' => round($pourcentage, 1)
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CeriBox - Contrôle Parental Intelligent</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="header-page">
            <h1>Contrôle Parental & Sécurité</h1>
            <span class="badge" style="background: #e67e22;">Analyseur de texte actif</span>
        </div>

        <div class="card">
            <h3>Analyseur d'anomalies de domaines</h3>
            <p style="color: #555;">
                L'algorithme a détecté des tentatives de connexion à des domaines dont l'orthographe 
                est proche de sites officiels. Cela peut indiquer une tentative de Phishing.
            </p>

            <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left;">
                        <th style="padding: 12px;">Domaine détecté</th>
                        <th>Ressemblance</th>
                        <th>Confiance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alertes)): ?>
                        <tr><td colspan="4" style="padding: 20px; text-align: center;">Aucune anomalie détectée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($alertes as $alerte): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; font-weight: bold; color: #e74c3c;"><?= $alerte['site_suspect'] ?></td>
                                <td style="color: #64748b;">Proche de : <?= $alerte['ressemble_a'] ?></td>
                                <td><span class="badge" style="background: #fee2e2; color: #ef4444;"><?= $alerte['taux'] ?>%</span></td>
                                <td>
                                    <form method="POST" action="ajouter_blacklist.php">
                                        <input type="hidden" name="domain" value="<?= $alerte['site_suspect'] ?>">
                                        <button type="submit" class="btn-blue" style="background: #1e293b; padding: 5px 10px;">
                                            Ajouter à la Blacklist
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
