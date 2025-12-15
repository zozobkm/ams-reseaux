<?php
require_once __DIR__."/../auth/require_login.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>DHCP</title><link rel="stylesheet" href="/ams-reseaux/assets/style.css"></head>
<body>
<?php include __DIR__."/../menu.php"; ?>
<div class="container">
<h1>DHCP</h1>
<p>Mode actuel : <strong><?= htmlspecialchars($_SESSION["mode"]) ?></strong></p>
<?php if($_SESSION["mode"]==="avance"): ?>
  <div class="card"><p>Zone avancée DHCP (à compléter)</p></div>
<?php endif; ?>
<div class="card"><p>Zone normal DHCP (à compléter)</p></div>
</div>
</body>
</html>
