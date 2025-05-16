<?php
session_start();
require_once ('../db.php'); // Connexion DB

// Sécurité : vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: https://esportify.alwaysdata.net/backend/login.php");
    exit();
}

$user_id = $_SESSION['user']['id']; // Récupérer l'ID de l'utilisateur
$role = $_SESSION['user']['role']; // Récupérer le rôle de l'utilisateur

// Récupérer les infos de l'utilisateur
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user || !is_array($user)) {
    echo "Utilisateur non trouvé.";
    exit();
}


// Sécuriser les valeurs récupérées
$username = isset($user['username']) ? htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8') : '';
$email = isset($user['email']) ? htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') : '';
$avatar = isset($user['avatar']) ? htmlspecialchars((string)$user['avatar'], ENT_QUOTES, 'UTF-8') : '';
$role_safe = htmlspecialchars((string)$role, ENT_QUOTES, 'UTF-8');
?>

<h2>Mon Profil (<?= $role_safe ?>)</h2>

<form action="https://esportify.alwaysdata.net/backend/update_profile.php" method="POST" enctype="multipart/form-data">
    <label>Nom :</label>
    <input type="text" name="username" value="<?= $username ?>"><br>

    <label>Email actuel :</label>
    <input type="email" value="<?= $email ?>" disabled><br>

    <label>Avatar actuel :</label><br>
    <?php if (!empty($avatar)): ?>
        <img src="<?= $avatar ?>" width="80"><br>
    <?php else: ?>
        Aucun avatar<br>
    <?php endif; ?>

    <label>Changer avatar :</label><br>
    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/gif"><br>

    <!-- Prévisualisation -->
    <img id="avatarPreview" src="#" alt="Aperçu de l'avatar" style="display:none; width:100px; margin-top:10px;"><br><br>

    <button type="submit">Mettre à jour</button>
</form>

<br>
<a href="https://esportify.alwaysdata.net/backend/reset_pass_request.php">Réinitialiser mon mot de passe</a><br>
<a href="https://esportify.alwaysdata.net/backend/change_email_request.php">Changer mon email</a><br>

<script>
document.getElementById('avatarInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('avatarPreview');

    preview.style.display = 'none';
    preview.src = '#';

    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Seuls les fichiers JPG, PNG et GIF sont autorisés.');
            event.target.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Fichier trop volumineux (max 2 Mo).');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
