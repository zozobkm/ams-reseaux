<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Configuration réseau AMS</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:20px;text-align:center}
h1{color:#333;margin-bottom:30px}
.card{background:white;display:inline-block;width:350px;padding:20px;margin:15px;border-radius:10px;box-shadow:0 0 5px #ccc;text-align:left}
.card h2{color:#0078d7;margin-bottom:10px}
a.btn{display:inline-block;background:#0078d7;color:white;text-decoration:none;padding:10px 20px;border-radius:5px;margin-top:10px}
a.btn:hover{background:#005fa3}
</style>
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

</body>
</html>
