<style>
nav{
    background:#0078d7;
    color:white;
    padding:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    box-shadow:0 2px 4px rgba(0,0,0,0.2);
    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:1000;
}
body{padding-top:60px;}

nav a{
    color:white;
    text-decoration:none;
    margin:0 20px;
    font-weight:bold;
    transition:0.3s;
}
nav a:hover{
    text-decoration:underline;
}
</style>

<nav>
    <a href="/ams-reseaux/index.php">Accueil</a>
    <a href="/ams-reseaux/templates/ip_edit.php">Modifier IP</a>
    <a href="/ams-reseaux/templates/dhcp.php">DHCP</a>
    <a href="/ams-reseaux/templates/dns.php">DNS</a>
</nav>
