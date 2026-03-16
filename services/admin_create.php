<?php
require_once "../auth/require_login.php";
require_once "../services/db.php";

// Sécurité : Seul le patron (Admin) peut créer des gens
if ($_SESSION['role'] !== 'admin') {
    die("Accès refusé. Seul l'administrateur peut créer des comptes.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $pass  = $_POST["pass"];
    $role  = $_POST["role"]; // 'user' ou 'admin'
    $user_linux = explode('@', $email)[0]; // on récupère 'alice' de 'alice@box.local'

    // 1. Hachage automatique
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    try {
        // 2. Insertion MySQL
        $stmt = $pdo->prepare("INSERT INTO box_users (email, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, $role]);

        // 3. Création Linux pour Postfix (S5)
        shell_exec("sudo adduser $user_linux --gecos '' --disabled-password");
        shell_exec("echo '$user_linux:$pass' | sudo chpasswd");

        echo "✅ Utilisateur $email créé avec succès (Web + Mail) !";
    } catch (Exception $e) {
        echo "❌ Erreur : " . $e->getMessage();
    }
}
?>

<form method="post" class="card">
    <h3>Créer un nouveau client FAI</h3>
    <input type="email" name="email" placeholder="Email (ex: alice@box.local)" required>
    <input type="password" name="pass" placeholder="Mot de passe" required>
    <select name="role">
        <option value="user">Utilisateur Normal</option>
        <option value="admin">Administrateur</option>
    </select>
    <button type="submit" class="btn-blue">Valider la création</button>
</form>
