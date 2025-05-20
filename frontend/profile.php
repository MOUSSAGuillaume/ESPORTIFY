<?php
session_start();
require_once ('../db.php');


if (!isset($_SESSION['user'])) {
    header("Location: https://esportify.alwaysdata.net/backend/login.php");
    exit();
}

$user = $_SESSION['user'];// Récupération de l'utilisateur depuis la session
$role = $user['role'];// Récupération du rôle de l'utilisateur

$username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user['email']?? '', ENT_QUOTES, 'UTF-8');
$role_safe = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
$avatar = htmlspecialchars($user['avatar'] ?? '', ENT_QUOTES, 'UTF-8');

$roleId = $user['role']; // entier : 1, 2 ou 4

$rolesMap = [
    4=> 'joueur',
    2 => 'organisateur',
    1 => 'admin'
];
$role = $rolesMap[$roleId] ?? 'joueur'; // Valeur de secours : 'joueur'

$dashboardMap = [
    'admin' => '/frontend/admin_dashboard.php',
    'organisateur' => '/frontend/organisateur_dashboard.php',
    'joueur' => '/frontend/joueur_dashboard.php',
];

$baseUrl = 'https://esportify.alwaysdata.net';
$dashboardUrl = $baseUrl . ($dashboardMap[$role] ?? '/frontend/joueur_dashboard.php');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Esportify - Espace Joueur</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/profile.css" />
</head>
<body>

<div class="wrapper">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="<?= $dashboardUrl ?>">
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                    </div>
                </a>
            </div>
        </nav>
    </header>

    <main id="dashboard-content">
        <h2>Mon Profil</h2>

        <form action="https://esportify.alwaysdata.net/backend/update_profile.php" method="POST" enctype="multipart/form-data">
            <label>Nom :</label>
            <input type="text" name="username" value="<?= $username ?>"><br>

            <label>Email actuel :</label>
            <input type="email" value="<?= $email ?>" disabled><br>

            <label>Avatar actuel :</label><br>
            <?php if (!empty($avatar)): ?>
                <img src="<?= $avatar ?>" width="80" alt="Avatar utilisateur"><br>
            <?php else: ?>
                Aucun avatar<br>
            <?php endif; ?>

            <label>Changer avatar :</label><br>
            <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/gif"><br>

            <img id="avatarPreview" src="#" alt="Aperçu de l'avatar" style="display:none; width:100px; margin-top:10px;"><br><br>
            <button type="submit">Mettre à jour</button>
        </form>

        <br>
        <button id="openPasswordModalBtn">Changer mon mot de passe</button><br><br>
        <a href="https://esportify.alwaysdata.net/backend/change_email_request.php">Changer mon email</a><br>
    </main>

    <footer>
        <nav>
            <span>Moussa Mehdi-Guillaume</span>
            <img src="../img/copyrighlogo.jpg" alt="Illustration copyright" />
            <ul>
                <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
                <li><a href="#mentions_legales">Mentions légales</a></li>
            </ul>
        </nav>
    </footer>
</div>

<!-- Modal popup changement de mot de passe -->
<div id="passwordModal">
    <div class="modal-content">
        <span class="close-btn" id="closePasswordModal">&times;</span>
        <h3>Changer mon mot de passe</h3>
        <form id="changePasswordForm">

            <label for="oldPassword">Ancien mot de passe :</label>
            <input type="password" id="oldPassword" name="oldPassword" required>

            <label for="newPassword">Nouveau mot de passe :</label>
            <input type="password" id="newPassword" name="newPassword" required>

            <label for="confirmPassword">Confirmer le mot de passe :</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>

            <button type="submit">Valider</button>
        </form>
        <div class="message" id="passwordMessage"></div>
    </div>
</div>

<script>
// Preview avatar
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

// Modal controls
const modal = document.getElementById('passwordModal');
const openBtn = document.getElementById('openPasswordModalBtn');
const closeBtn = document.getElementById('closePasswordModal');
const messageBox = document.getElementById('passwordMessage');

openBtn.onclick = () => {
    modal.style.display = 'block';
    messageBox.textContent = '';
    document.getElementById('changePasswordForm').reset();
};

closeBtn.onclick = () => {
    modal.style.display = 'none';
};

// Fermer le modal en cliquant hors de la fenêtre
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
};

// AJAX pour envoyer le formulaire changement mdp
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const oldPassword = document.getElementById('oldPassword').value.trim();
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    if (newPassword.length < 8) {
        messageBox.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
        messageBox.className = 'message error';
        return;
    }
    if (!/[A-Z]/.test(newPassword)) {
        messageBox.textContent = "Le mot de passe doit contenir au moins une majuscule.";
        messageBox.className = 'message error';
        return;
    }
    if (!/[0-9]/.test(newPassword)) {
        messageBox.textContent = "Le mot de passe doit contenir au moins un chiffre.";
        messageBox.className = 'message error';
        return;
    }
    if (newPassword !== confirmPassword) {
        messageBox.textContent = "Les mots de passe ne correspondent pas.";
        messageBox.className = 'message error';
        return;
    }

    // Envoi AJAX
    fetch('https://esportify.alwaysdata.net/backend/change_pass.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},// JSON
        credentials: 'include', // pour envoyer cookies / session
        body: JSON.stringify({ oldPassword, newPassword, confirmPassword })// JSON.stringify

    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageBox.textContent = data.message;
            messageBox.className = 'message success';
            setTimeout(() =>
        modal.style.display = 'none', 2000);
        } else {
        messageBox.textContent = data.message;
        messageBox.className = 'message error';
        }
    })
    .catch(() => {
        messageBox.textContent = "Erreur serveur, veuillez réessayer plus tard.";
        messageBox.className = 'message error';
    });
});
</script>

</body> </html>