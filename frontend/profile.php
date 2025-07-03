<?php
include_once(__DIR__ . '/../db.php');
if (!isset($_SESSION['user'])) {
    header("Location: /index.php?page=connexion");
    exit();
}

$user = $_SESSION['user'];
$roleId = $user['role'];
$username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
$avatar = htmlspecialchars($user['avatar'] ?? '', ENT_QUOTES, 'UTF-8');

$rolesMap = [4 => 'joueur', 2 => 'organisateur', 1 => 'admin'];
$role = $rolesMap[$roleId] ?? 'joueur';

$dashboardMap = [
    'admin' => '/index.php?page=admin_dashboard',
    'organisateur' => '/index.php?page=organisateur_dashboard',
    'joueur' => '/index.php?page=joueur_dashboard',
];
$baseUrl = 'https://esportify.alwaysdata.net';
$dashboardUrl = $baseUrl . ($dashboardMap[$role] ?? '/index.php?page=joueur_dashboard');
?>

<link rel="stylesheet" href="../css/profile.css">

<div class="container my-5" style="max-width: 700px;">
    <div class="card shadow"  style= "background: rgb(26, 7, 56); color: wheat;">
        <div class="card-header bg-primary text-white text-center fs-4 d-flex justify-content-between align-items-center">
            <span>Mon Profil</span>
            <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-light btn-sm ms-auto">← Retour Dashboard</a>
        </div>
        <div class="card-body">
            <form action="/index.php?page=update_profile.php" method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-12">
                    <label for="username" class="form-label fw-semibold">Nom :</label>
                    <input type="text" name="username" id="username" value="<?= $username ?>" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Email actuel :</label>
                    <input type="email" value="<?= $email ?>" disabled class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Avatar actuel :</label><br>
                    <?php if (!empty($avatar)): ?>
                        <img src="<?= $avatar ?>" width="80" alt="Avatar utilisateur" class="rounded-circle border mb-2">
                    <?php else: ?>
                        <span class="text-white" text-white>Aucun avatar</span>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="avatarInput" class="form-label fw-semibold">Changer avatar :</label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/gif" class="form-control">
                    <img id="avatarPreview" src="#" alt="Aperçu de l'avatar" style="display:none; width:90px; margin-top:10px;" class="rounded">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success px-4">Mettre à jour</button>
                </div>
            </form>
            <hr>
            <div class="d-flex flex-column align-items-center gap-2">
                <button id="openPasswordModalBtn" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#passwordModal">Changer mon mot de passe</button>
                <a href="/change_email" class="btn btn-outline-secondary w-100">Changer mon email</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap pour changement mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <form id="changePasswordForm" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Changer mon mot de passe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="oldPassword" class="form-label">Ancien mot de passe :</label>
                    <input type="password" id="oldPassword" name="oldPassword" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">Nouveau mot de passe :</label>
                    <input type="password" id="newPassword" name="newPassword" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe :</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required>
                </div>
                <div class="alert d-none" id="passwordMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Valider</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Avatar preview
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

// AJAX changement mot de passe Bootstrap only
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const oldPassword = document.getElementById('oldPassword').value.trim();
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();
    const messageBox = document.getElementById('passwordMessage');

    // Validation front simple
    if (newPassword.length < 8) {
        showError("Le mot de passe doit contenir au moins 8 caractères."); return;
    }
    if (!/[A-Z]/.test(newPassword)) {
        showError("Le mot de passe doit contenir au moins une majuscule."); return;
    }
    if (!/[0-9]/.test(newPassword)) {
        showError("Le mot de passe doit contenir au moins un chiffre."); return;
    }
    if (newPassword !== confirmPassword) {
        showError("Les mots de passe ne correspondent pas."); return;
    }

    fetch('https://esportify.alwaysdata.net/backend/change_pass.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'include',
        body: JSON.stringify({ oldPassword, newPassword, confirmPassword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                const modalEl = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                if(modalEl) modalEl.hide();
            }, 1800);
        } else {
            showError(data.message);
        }
    })
    .catch(() => {
        showError("Erreur serveur, veuillez réessayer plus tard.");
    });

    function showError(msg) {
        messageBox.textContent = msg;
        messageBox.className = 'alert alert-danger';
        messageBox.classList.remove('d-none');
    }
    function showSuccess(msg) {
        messageBox.textContent = msg;
        messageBox.className = 'alert alert-success';
        messageBox.classList.remove('d-none');
    }
});
</script>