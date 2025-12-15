<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Configuration réseau AMS</title>
<link rel="stylesheet" href="/ams-reseaux/assets/style.css">

</head>
<body>

<h1>Tableau de bord - Configuration Réseau</h1>

<div class="card">
    <h2>Adresse IP du serveur</h2>
    <p>Configurer ou modifier l’adresse IP de l’interface <b>eth1</b>.<br>
    Cette adresse doit être fixe (ex : 192.168.10.1).</p>
    <a href="ip_edit.php" class="btn">Modifier l’adresse IP</a>
</div>

<div class="card">
    <h2>Service DHCP</h2>
    <p>Configurer le serveur DHCP pour attribuer des adresses automatiques<br>
    (par exemple : 192.168.10.10 → 192.168.10.50).</p>
    <a href="dhcp.php" class="btn">Configurer le DHCP</a>
</div>

<div class="card">
    <h2>DNS </h2>
    <p>Configurer le serveur DNS local (association noms ↔ IP).</p>
    <a href="dns.php" class="btn">Configurer le DNS</a>
</div>
    <div class="card">
    <h2>NAT </h2>
    <p>Configurer le NAt.</p>
    <a href="nat.php" class="btn">Configurer le NAt</a>
</div>

</body>
</html>
