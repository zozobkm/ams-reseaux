
<?php include('menu.php'); ?>

<h2>Activation du NAT</h2>

<form method="post">
    <button type="submit" name="activer">Activer le NAT</button>
</form>

<?php
if (isset($_POST['activer'])) {

    $cmd = "sudo /var/www/html/ams-reseaux/scripts/config_nat.sh";
    $resultat = shell_exec("$cmd 2>&1");

    echo "<hr><pre>$resultat</pre>";
}
?>
