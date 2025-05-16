<?php
include_once("../db.php");
session_start();

// Vérification des privilèges admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 1) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php");
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

    header("Location: https://esportify.alwaysdata.net/frontend/gestion_utilisateurs.php?success=" . urlencode($msg));
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

    header("Location: https://esportify.alwaysdata.net/frontend/gestion_utilisateurs.php?success=" . urlencode($msg));
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

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Utilisateurs</title>
  <link rel="stylesheet" href="https://esportify.alwaysdata.net/style.css/dashboard_style.css" />
</head>
<body>

<main id="dashboard-content">
  <header>
    <nav class="custom-navbar">
      <div class="logo-wrapper">
        <a href="https://esportify.alwaysdata.net/frontend/admin_dashboard.php">
          <div class="logo-container">
            <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
          </div>
        </a>
        <div class="semi-circle-outline"></div>
      </div>
    </nav>
  </header>

  <section class="dashboard">
    <h1>Gestion des Utilisateurs</h1>
    <div class="dashboard-links">
            <a href="https://esportify.alwaysdata.net/frontend/gestion_admin.php" class="btn">Gestion des Events</a>
            <a href="https://esportify.alwaysdata.net/frontend/gestion_utilisateurs.php" class="btn">Gérer les utilisateurs</a>
            <a href="https://esportify.alwaysdata.net/frontend/gestion_newsletters.php" class="btn">Gestion des newsletters</a>
            <a href="https://esportify.alwaysdata.net/backend/logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </section>

    <?php if (isset($_GET['success'])): ?>
      <div class="msg success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if ($editUser): ?>
    <div class="form-wrapper">
        <h2>Modifier l'utilisateur : <?= htmlspecialchars($editUser['username']) ?></h2>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <input type="text" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
            <button type="submit" name="update_user" class="button">Enregistrer</button>
            <a href="https://esportify.alwaysdata.net/frontend/gestion_utilisateurs.php" class="button delete">Annuler</a>
        </form>
    </div>
    <?php endif; ?>

    <table>
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
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <span class="status <?= $user['actif'] ? 'active' : 'inactive' ?>">
                        <?= $user['actif'] ? 'Actif' : 'Inactif' ?>
                    </span>
                </td>
                <td>
                    <?php if ($user['actif']): ?>
                        <a href="?id=<?= $user['id'] ?>&action=disable" class="button delete">Désactiver</a>
                    <?php else: ?>
                        <a href="?id=<?= $user['id'] ?>&action=enable" class="button">Réactiver</a>
                    <?php endif; ?>
                    <a href="?id=<?= $user['id'] ?>&action=edit" class="button">Modifier</a>
                    <a href="?id=<?= $user['id'] ?>&action=delete" class="button delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
  </section>

  <footer>
    <nav>
      <span>Moussa Mehdi-Guillaume</span>
      <img src="../img/copyrighlogo.jpg" alt="Copyright" />
      <ul>
        <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
        <li><a href="#mentions_legales">Mentions légales</a></li>
      </ul>
    </nav>
  </footer>
</main>

</body>
</html>
