<?php
include_once(__DIR__ . '/../db.php');

// Vérification des privilèges admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 1) {
    header('Location: /index.php?page=frontend/connexion.php');
    exit;
}

// Mettre à jour l'activité de l'utilisateur
$userId = $_SESSION['user']['id'];
mysqli_query($conn, "UPDATE users SET last_activity = NOW() WHERE id = $userId");

// Traitement des actions d'activation / désactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === "disable") {
        $sql = "UPDATE users SET actif = 0 WHERE id = $id";
        $msg = "Utilisateur désactivé avec succès.";
    } elseif ($action === "enable") {
        $sql = "UPDATE users SET actif = 1 WHERE id = $id";
        $msg = "Utilisateur réactivé avec succès.";
    } elseif ($action === "delete") {
        if ($id === $userId) {
            $msg = "❌ Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            $sql = "DELETE FROM users WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $msg = "Utilisateur supprimé avec succès.";
            } else {
                $msg = "❌ Erreur lors de la suppression de l'utilisateur.";
            }
        }
    }

    if (isset($sql)) {
        mysqli_query($conn, $sql);
    }

    header("Location: /gestion_utilisateurs?success=" . urlencode($msg));
    exit;
}

// Traitement de la modification d'un utilisateur
if (isset($_POST['update_user']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "UPDATE users SET username = '$username', email = '$email' WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        $msg = "Utilisateur modifié avec succès.";
    } else {
        $msg = "❌ Erreur lors de la mise à jour de l'utilisateur.";
    }

    header("Location: /gestion_utilisateurs?success=" . urlencode($msg));
    exit;
}

// Récupération des données pour modification
$editUser = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE id = $editId");
    if ($res && mysqli_num_rows($res) > 0) {
        $editUser = mysqli_fetch_assoc($res);
    }
}

// Récupérer tous les utilisateurs
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY username ASC");
?>

<link rel="stylesheet" href="../css/dashboard.css" />

<main id="dashboard-content" class="container my-4">
    <section class="dashboard mb-4">
        <h1>Gestion des Utilisateurs</h1>
        <div class="dashboard-links mb-3">
            <a href="/admin_dashboard" class="btn btn-outline-info rounded-pill px-4 fw-bold">Dashboard</a>
            <a href="/gestion_admin" class="btn btn-outline-info rounded-pill px-4 fw-bold">Gestion des Events</a>
            <a href="/gestion_newsletters" class="btn btn-outline-info rounded-pill px-4 fw-bold">Gestion des newsletters</a>
        </div>
    </section>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if ($editUser): ?>
        <div class="card mb-4 shadow">
            <div class="card-header bg-primary text-white">
                Modifier l'utilisateur : <?= htmlspecialchars($editUser['username']) ?>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <div class="col-md-6">
                        <label class="form-label">Pseudo</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editUser['username']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                    </div>
                    <div class="col-12 d-flex gap-2 mt-3">
                        <button type="submit" name="update_user" class="btn btn-success">Enregistrer</button>
                        <a href="/gestion_utilisateurs" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle rounded-3 overflow-hidden">
            <thead>
                <tr>
                    <th>Pseudo</th>
                    <th>Email</th>
                    <th>Statut du compte</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['actif']): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex flex-wrap gap-2">
                            <?php if ($user['actif']): ?>
                                <a href="?id=<?= $user['id'] ?>&action=disable" class="btn btn-outline-warning btn-sm">Désactiver</a>
                            <?php else: ?>
                                <a href="?id=<?= $user['id'] ?>&action=enable" class="btn btn-outline-success btn-sm">Réactiver</a>
                            <?php endif; ?>
                            <a href="?id=<?= $user['id'] ?>&action=edit" class="btn btn-outline-primary btn-sm">Modifier</a>
                            <a href="?id=<?= $user['id'] ?>&action=delete" class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>