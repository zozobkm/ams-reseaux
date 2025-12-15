<?php
if(session_status()===PHP_SESSION_NONE){session_start();}
?>
<nav class="topnav">
  <a href="/ams-reseaux/dahboard/index.php">Dashboard</a>
  <a href="/ams-reseaux/services/dhcp.php">DHCP</a>
  <a href="/ams-reseaux/services/dns.php">DNS</a>
  <a href="/ams-reseaux/services/nat.php">NAT</a>
  <a href="/ams-reseaux/services/ftp.php">FTP</a>
  <a href="/ams-reseaux/services/mail.php">Mail</a>
  <a href="/ams-reseaux/services/forum.php">Forum</a>

  <?php if(isset($_SESSION["role"]) && $_SESSION["role"]==="admin"): ?>
    <a href="/ams-reseaux/admin/users.php">Admin</a>
  <?php endif; ?>

  <span class="spacer"></span>
  <span class="badge"><?= htmlspecialchars($_SESSION["mode"]??"normal") ?></span>
  <a class="logout" href="/ams-reseaux/auth/logout.php">Déconnexion</a>
</nav>
