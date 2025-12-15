<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/ams-reseaux/assets/style.css">
    <title>Configuration DNS</title>
</head>

<?php include('menu.php'); ?>

<body>
<h2>Configurer un domaine DNS</h2>

<form method="post">
    <label>Domaine :</label>
    <input type="text" name="domaine" placeholder="ex : zozo" required>
    <input type="submit" name="configurer" value="Configurer">
</form>

<?php
if(isset($_POST['configurer'])){
    $domaine = trim($_POST['domaine']);

    if($domaine !== ""){
        echo "<h3>Résultat :</h3><pre>";
        echo shell_exec("sudo bash /var/www/html/ams-reseaux/scripts/config_dns.sh $domaine 2>&1");
        echo "</pre>";
    } else {
        echo "<p style='color:red;'>Erreur : domaine vide.</p>";
    }
}
?>

</body>
</html>
