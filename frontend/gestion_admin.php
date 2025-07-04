<?php
include_once(__DIR__ . '/../db.php');

// Vérifier que l'utilisateur est un administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 1) {
    header('Location: /connexion');
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
        header("Location: /gestion_admin?success=Tournoi mis à jour et validé.");
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
    header("Location: /gestion_admin?success=Tournoi supprimé avec succès.");
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
        header("Location: /gestion_admin?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE events SET status = 'refusé' WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        header("Location: /gestion_admin?success=Tournoi refusé.");
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

    header("Location: /gestion_admin?success=Statut du tournoi mis à jour.");
    exit;
}

// Récupération des tournois avec le nombre d'inscrits
$events = $conn->query("SELECT e.*,
    (SELECT COUNT(*) FROM inscriptions i WHERE i.event_id = e.id) AS inscrits
    FROM events e
    ORDER BY e.event_date DESC
");

?>

<link rel="stylesheet" href="../css/dashboard.css">

<main id="dashboard-content" class="container my-4">
    <section class="dashboard mb-4">
        <h1>Gestion Admin</h1>
        <div class="dashboard-links mb-3">
            <a href="/admin_dashboard" class="btn btn-outline-info rounded-pill px-4 fw-bold">Dashboard</a>
            <a href="/gestion_utilisateurs" class="btn btn-outline-info rounded-pill px-4 fw-bold">Gérer les utilisateurs</a>
            <a href="/gestion_newsletter" class="btn btn-outline-info rounded-pill px-4 fw-bold">Gestion des newsletters</a>
        </div>
        <?= isset($_GET['success']) ? "<div class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</div>" : '' ?>
    </section>

    <h3 class="mb-3">Events</h3>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle rounded-3 overflow-hidden">
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
                                <button class="btn btn-outline-warning btn-sm modify-btn mb-1" data-id="<?= $t['id'] ?>">Modifier</button>
                            <?php endif; ?>
                            <a href="/gestion_admin?delete=<?= $t['id'] ?>" class="btn btn-outline-danger btn-sm mb-1" onclick="return confirm('Supprimer ce tournoi ?')">Supprimer</a>
                            <?php if (in_array(strtolower($t['status']), ['à confirmer', 'à refuser'])): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="valider">
                                    <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm mb-1">Valider</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="refuser">
                                    <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm mb-1">Refuser</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="event_id" value="<?= $t['id'] ?>">
                                <select name="status_action" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                    <option value="en cours" <?= $t['status'] === 'en cours' ? 'selected' : '' ?>>En cours</option>
                                    <option value="terminé" <?= $t['status'] === 'terminé' ? 'selected' : '' ?>>Terminé</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-outline-info btn-sm inscriptions-btn" data-id="<?= $t['id'] ?>">Voir (<?= $t['inscrits'] ?? 0 ?>)</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Popups : Bootstrap style -->
<div id="editPopup" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'event</h5>
                <button type="button" class="btn-close close-button" aria-label="Fermer"></button>
            </div>
            <form id="editForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="tournoi_id" id="editTournoiId">
                    <div class="mb-3">
                        <label for="editNbMax" class="form-label">Nombre de joueurs :</label>
                        <input type="number" name="nb_max_participants" id="editNbMax" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDate" class="form-label">Date du tournoi :</label>
                        <input type="date" name="date_event" id="editDate" class="form-control" required>
                    </div>
                    <input type="hidden" name="action" value="update_event">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Valider les changements</button>
                    <button type="button" class="btn btn-secondary close-button" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="inscriptionsPopup" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Liste des inscrits</h5>
                <button type="button" class="btn-close close-inscriptions" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="inscriptionsList">Chargement...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Adaptation du JS pour Bootstrap modals
    document.querySelectorAll('.modify-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = this.dataset.id;
            const date = row.children[2].textContent.trim();
            const nbMax = row.children[4].textContent.trim();
            document.getElementById('editTournoiId').value = id;
            document.getElementById('editNbMax').value = nbMax;
            document.getElementById('editDate').value = date;
            // Bootstrap modal
            const modal = new bootstrap.Modal(document.getElementById('editPopup'));
            modal.show();
        });
    });
    document.querySelectorAll('.close-button').forEach(btn => {
        btn.onclick = () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPopup'));
            if (modal) modal.hide();
        }
    });
    document.querySelectorAll('.inscriptions-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const eventId = btn.dataset.id;
            const popup = new bootstrap.Modal(document.getElementById('inscriptionsPopup'));
            const listDiv = document.getElementById('inscriptionsList');
            listDiv.innerHTML = "Chargement...";
            fetch(`https://esportify.alwaysdata.net/backend/get_inscriptions.php?event_id=${eventId}`)
                .then(res => res.text())
                .then(html => listDiv.innerHTML = html);
            popup.show();
        });
    });
    document.querySelectorAll('.close-inscriptions').forEach(btn => {
        btn.onclick = () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('inscriptionsPopup'));
            if (modal) modal.hide();
        }
    });

    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("valider-inscription") || e.target.classList.contains("refuser-inscription")) {
            const id = e.target.dataset.id;
            const action = e.target.classList.contains("valider-inscription") ? "confirmé" : "refusé";

            // Envoi de la requête pour mettre à jour le statut dans la base de données
            fetch(`https://esportify.alwaysdata.net/backend/update_inscription_status.php`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
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
        });
    });

    document.querySelectorAll('.close-inscriptions').forEach(btn => {
        btn.onclick = () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('inscriptionsPopup'));
            if (modal) modal.hide();
        }
    });
</script>