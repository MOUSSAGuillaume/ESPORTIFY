<?php
session_start();
require_once ('../db.php'); // Connexion DB

// Sécurité : vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];// Récupérer l'ID de l'utilisateur
$role = $_SESSION['user']['role'];// Récupérer le rôle de l'utilisateur

// Récupérer les infos de l'utilisateur
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}
?>

<h2>Mon Profil (<?= htmlspecialchars($role) ?>)</h2>

<form action="update_profile.php" method="POST" enctype="multipart/form-data">
    <label>Nom :</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>

    <label>Email actuel :</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled><br>

    <label>Avatar actuel :</label><br>
    <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar']) ?>" width="80"><br>
    <?php else: ?>
        Aucun avatar<br>
    <?php endif; ?>

    <label>Changer avatar :</label>
    <input type="file" name="avatar"><br><br>

    <button type="submit">Mettre à jour</button>
</form>

<br>
<a href="/ESPORTIFY/backend/reset_pass_request.php">Réinitialiser mon mot de passe</a><br>
<a href="change_email_request.php">Changer mon email</a><br> <!-- Lien vers le formulaire de changement d'email -->
<a href="delete_account.php">Supprimer mon compte</a>

