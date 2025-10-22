<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Configuration DNS</title></head>
<body>
<h2>Configurer un domaine DNS</h2>
<form method="post">
<label>Domaine :</label>
<input type="text" name="domaine" required>
<input type="submit" name="configurer" value="Configurer">
</form>

<?php
if(isset($_POST['configurer'])){
 $domaine=$_POST['domaine'];
 echo "<pre>";
 echo shell_exec("sudo bash ../scripts/config_dns.sh $domaine 2>&1");
 echo "</pre>";
}
?>
</body>
</html>
