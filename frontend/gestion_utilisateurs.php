<?php
include_once("../db.php");
session_start();

// Vérification des privilèges admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: /ESPORTIFY/frontend/connexion.php");
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

    header("Location: /ESPORTIFY/frontend/gestion_utilisateurs.php?success=" . urlencode($msg));
    exit;
}

// Traitement de la modification d'un utilisateur
if (isset($_POST['update_user']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $pseudo = mysqli_real_escape_string($conn, $_POST['pseudo']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "UPDATE users SET pseudo = '$username', email = '$email' WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        $msg = "Utilisateur modifié avec succès.";
    } else {
        $msg = "❌ Erreur lors de la mise à jour de l'utilisateur.";
    }

    header("Location: /ESPORTIFY/frontend/gestion_utilisateurs.php?success=" . urlencode($msg));
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
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY pseudo ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Utilisateurs</title>
  <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css" />
</head>
<body>

<div class="console-overlay" id="console-overlay">
  <div class="console-text" id="console-text"></div>
</div>

<main class="hidden" id="dashboard-content">
  <header>
    <nav class="custom-navbar">
      <div class="logo-wrapper">
        <a href="../frontend/gestion_admin.php">
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

    <?php if (isset($_GET['success'])): ?>
      <div class="msg success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if ($editUser): ?>
    <div class="form-wrapper">
        <h2>Modifier l'utilisateur : <?= htmlspecialchars($editUser['pseudo']) ?></h2>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <input type="text" name="pseudo" value="<?= htmlspecialchars($editUser['pseudo']) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
            <button type="submit" name="update_user" class="button">Enregistrer</button>
            <a href="/ESPORTIFY/frontend/gestion_utilisateurs.php" class="button delete">Annuler</a>
        </form>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Pseudo</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($user['pseudo']) ?></td>
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

<script>
  const consoleText = document.getElementById("console-text");
  const overlay = document.getElementById("console-overlay");
  const dashboard = document.getElementById("dashboard-content");

  const lines = [
    "Chargement de la gestion des utilisateurs...",
    "Vérification des privilèges...",
    "Interface Admin prête !"
  ];

  let index = 0;
  function typeLine() {
    if (index < lines.length) {
      consoleText.textContent += lines[index] + "\n";
      index++;
      setTimeout(typeLine, 600);
    } else {
      setTimeout(() => {
        overlay.remove();
        const flash = document.createElement("div");
        flash.classList.add("screen-flash");
        document.body.appendChild(flash);
        setTimeout(() => {
          flash.remove();
          dashboard.classList.remove("hidden");
        }, 600);
      }, 1000);
    }
  }
  typeLine();
</script>

</body>
</html>
