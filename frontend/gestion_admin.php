<?php
include_once("../db.php");
session_start();

// Vérifier que l'utilisateur est un administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 1) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Traitement du formulaire de modification + validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_event') {
    $tournoiId = intval($_POST['tournoi_id']);
    $nbMax = intval($_POST['nb_max_participants']);
    $date = $_POST['date_event'];

    $stmt = $conn->prepare("UPDATE events SET nb_max_participants = ?, event_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $nbMax, $date, $tournoiId);
    if ($stmt->execute()) {
        header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Tournoi mis à jour et validé.");
        exit;
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Suppression d'un tournoi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Tournoi supprimé avec succès.");
    exit;
}

// Validation / Refus d'un tournoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);
    $action = $_POST['action'];

    if ($action === 'valider') {
        $stmt = $conn->prepare("UPDATE events SET status = 'accepté' WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE events SET status = 'refusé' WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php.php?success=Tournoi refusé.");
        exit;
    }
}

// Changement de statut (en cours / terminé)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_action'], $_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);
    $statusAction = $_POST['status_action'];

    $stmt = $conn->prepare("UPDATE events SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $statusAction, $eventId);
    $stmt->execute();

    header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Statut du tournoi mis à jour.");
    exit;
}

// Récupération des tournois avec le nombre d'inscrits
$events = $conn->query("SELECT e.*,
    (SELECT COUNT(*) FROM inscriptions i WHERE i.event_id = e.id) AS inscrits
    FROM events e
    ORDER BY e.event_date DESC
");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Admin</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style.css/dashboard_style.css">
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
        <h1>Gestion Admin</h1>
        <div class="dashboard-links">
            <a href="https://esportify.alwaysdata.net/frontend/gestion_admin.php" class="btn">Gestion des Events</a>
            <a href="https://esportify.alwaysdata.net/frontend/gestion_utilisateurs.php" class="btn">Gérer les utilisateurs</a>
            <a href="https://esportify.alwaysdata.net/frontend/gestion_newsletters.php" class="btn">Gestion des newsletters</a>
            <a href="https://esportify.alwaysdata.net/backend/logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </section>

        <?= isset($_GET['success']) ? "<div class='msg success'>" . htmlspecialchars($_GET['success']) . "</div>" : '' ?>

        <h3>Events</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom du jeu</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Places</th>
                    <th>Actions</th>
                    <th>Nb d'inscrits</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($t = mysqli_fetch_assoc($events)): ?>
                <tr>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($t['event_date']))) ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                    <td><?= intval($t['nb_max_participants']) ?></td>
                    <td class="actions-cell">
                        <?php if (strtolower(trim($t['status'])) !== 'accepté' && strtolower(trim($t['status'])) !== 'refusé'): ?>
                            <button class="button modify-btn" data-id="<?= $t['id'] ?>">Modifier</button>
                        <?php endif; ?>
                        <a href="https://esportify.alwaysdata.net/frontend/gestion_admin.php?delete=<?= $t['id'] ?>" class="button delete" onclick="return confirm('Supprimer ce tournoi ?')">Supprimer</a>
                        <?php if (in_array(strtolower($t['status']), ['à confirmer', 'à refuser'])): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="valider">
                                <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                                <button type="submit" class="button">Valider</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="refuser">
                                <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                                <button type="submit" class="button delete">Refuser</button>
                            </form>
                        <?php endif; ?>
                        
                        <!-- Nouveau menu déroulant pour changer le statut -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                            <select name="status_action" onchange="this.form.submit()">
                                <option value="en cours" <?= $t['status'] === 'en cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="terminé" <?= $t['status'] === 'terminé' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <button class="button inscriptions-btn" data-id="<?= $t['id'] ?>">Voir (<?= $t['inscrits'] ?? 0 ?>)</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <footer>
        <nav>
            <span>Moussa Mehdi-Guillaume</span>
            <img src="../img/copyrighlogo.jpg" alt="Copyright">
            <ul>
                <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
                <li><a href="#mentions_legales">Mentions légales</a></li>
            </ul>
        </nav>
    </footer>
</main>

<!-- Popups -->
<div id="editPopup" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Modifier l'event</h2>
        <form id="editForm" method="POST">
            <input type="hidden" name="tournoi_id" id="editTournoiId">
            <label for="nb_max">Nombre de joueurs :</label>
            <input type="number" name="nb_max_participants" id="editNbMax" required>
            <label for="date">Date du tournoi :</label>
            <input type="date" name="date_event" id="editDate" required>
            <input type="hidden" name="action" value="update_event">
            <button type="submit">Valider les changements</button>
        </form>
    </div>
</div>

<div id="inscriptionsPopup" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-inscriptions">&times;</span>
        <h2>Liste des inscrits</h2>
        <div id="inscriptionsList">Chargement...</div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-news').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const contentRow = document.getElementById('news-' + id);
        if (contentRow.style.display === 'none') {
            contentRow.style.display = 'table-row';
            btn.textContent = 'Cacher';
        } else {
            contentRow.style.display = 'none';
            btn.textContent = 'Voir';
        }
    });
});

document.querySelectorAll('.modify-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const row = this.closest('tr');
        const id = this.dataset.id;
        const date = row.children[2].textContent.trim();
        const nbMax = row.children[4].textContent.trim();
        document.getElementById('editTournoiId').value = id;
        document.getElementById('editNbMax').value = nbMax;
        document.getElementById('editDate').value = date;
        document.getElementById('editPopup').style.display = 'flex';
    });
});
document.querySelector('.close-button').onclick = () => document.getElementById('editPopup').style.display = 'none';

document.querySelectorAll('.inscriptions-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const eventId = btn.dataset.id;
        const popup = document.getElementById('inscriptionsPopup');
        const listDiv = document.getElementById('inscriptionsList');
        listDiv.innerHTML = "Chargement...";
        fetch(`https://esportify.alwaysdata.net/backend/get_inscriptions.php?event_id=${eventId}`)
            .then(res => res.text())
            .then(html => listDiv.innerHTML = html);
        popup.style.display = 'flex';
    });
});
document.querySelector('.close-inscriptions').onclick = () => document.getElementById('inscriptionsPopup').style.display = 'none';

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("valider-inscription") || e.target.classList.contains("refuser-inscription")) {
        const id = e.target.dataset.id;
        const action = e.target.classList.contains("valider-inscription") ? "confirmé" : "refusé";
        
        // Envoi de la requête pour mettre à jour le statut dans la base de données
        fetch(`https://esportify.alwaysdata.net/backend/update_inscription_status.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `inscription_id=${id}&action=${action}`
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            updateButtons(id, action);
        });
    }
});

function updateButtons(id, action) {
    const row = document.querySelector(`tr[data-id='${id}']`);
    if (!row) return;

    // Supprimer les boutons Valider/Refuser
    row.querySelectorAll(".valider-inscription, .refuser-inscription").forEach(btn => btn.remove());

    // Mettre à jour le statut dans la cellule prévue
    const statusCell = row.querySelector(".status-cell");
    if (statusCell) {
        statusCell.textContent = action.charAt(0).toUpperCase() + action.slice(1); // "Confirmé" ou "Refusé"
    }
}

// Gérer l'affichage dans le popup des inscriptions
document.querySelectorAll('.inscriptions-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const eventId = btn.dataset.id;
        const popup = document.getElementById('inscriptionsPopup');
        const listDiv = document.getElementById('inscriptionsList');
        listDiv.innerHTML = "Chargement...";

        fetch(`https://esportify.alwaysdata.net/backend/get_inscriptions.php?event_id=${eventId}`)
            .then(res => res.text())
            .then(html => listDiv.innerHTML = html);
        popup.style.display = 'flex';
    });
});

document.querySelector('.close-inscriptions').onclick = () => document.getElementById('inscriptionsPopup').style.display = 'none';
</script>

</body>
</html>
