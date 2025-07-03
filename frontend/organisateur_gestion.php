<?php
include_once(__DIR__ . '/../db.php');

// Authentification organisateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 2) {
    header("Location: /index.php?page=connexion");
    exit;
}

$username = $_SESSION['user']['pseudo'];
$user_id = $_SESSION['user']['id'];

// Ajout d'événement
$msg_evenement = "";
if (isset($_POST['submit_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = $_POST['event_date'];
    $status = 'en attente';
    $nb_max_participants = 0;

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, status, created_by, created_at, nb_max_participants)
                            VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    if ($stmt) {
        $stmt->bind_param("ssssii", $title, $description, $event_date, $status, $user_id, $nb_max_participants);
        $msg_evenement = $stmt->execute() ? "✅ Événement ajouté avec succès." : "❌ Erreur lors de l'ajout : " . $stmt->error;
        $stmt->close();
    } else {
        $msg_evenement = "❌ Erreur de préparation de la requête.";
    }
}

// Edition event (nb places & date)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_event') {
    $event_id = intval($_POST['event_id']);
    $nbMax = intval($_POST['nb_max_participants']);
    $date = $_POST['date_event'];
    $stmt = $conn->prepare("UPDATE events SET nb_max_participants = ?, event_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $nbMax, $date, $event_id);
    if ($stmt->execute()) {
        header("Location: /organisateur_gestion?success=Tournoi mis à jour et validé.");
        exit;
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Statut tournoi
if (isset($_POST['action'], $_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $action = $_POST['action'];
    if ($action === 'validé') {
        $stmt = $conn->prepare("UPDATE events SET status = 'à confirmer' WHERE id = ?");
    } elseif ($action === 'refusé') {
        $stmt = $conn->prepare("UPDATE events SET status = 'à refuser' WHERE id = ?");
    }
    if (isset($stmt)) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        header("Location: /organisateur_gestion?success=Tournoi $action.");
        exit;
    }
}

// Récupération des événements + nb inscrits
$events = $conn->query("SELECT e.*, (SELECT COUNT(*) FROM inscriptions i WHERE i.event_id = e.id) AS inscrits FROM events e ORDER BY e.event_date DESC");
?>

<link rel="stylesheet" href="../css/dashboard.css">

<main class="container my-5">
    <!-- Header + liens -->
    <section class="mb-4">
        <h1 class="mb-3">Gestion Organisateur</h1>
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <div class="mb-3 d-flex gap-3 flex-wrap">
            <a href="/organisateur_dashboard" class="btn btn-outline-info rounded-pill fw-bold">Dashboard</a>
            <a href="/gestion_newsletters" class="btn btn-outline-info rounded-pill fw-bold">Gestion des newsletters</a>
        </div>
    </section>

    <!-- Table events -->
    <h3>Événements</h3>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle custom-gradient-table">
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
            <?php while ($e = mysqli_fetch_assoc($events)) : ?>
                <tr>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($e['event_date']))) ?></td>
                    <td><?= htmlspecialchars($e['status']) ?></td>
                    <td><?= intval($e['nb_max_participants']) ?></td>
                    <td>
                        <?php if ($e['status'] !== 'accepté' && $e['status'] !== 'refusé'): ?>
                            <button class="btn btn-sm btn-outline-primary modify-btn mb-1" data-id="<?= $e['id'] ?>">Modifier</button>
                        <?php endif; ?>
                        <!-- Validation/Refus -->
                        <?php if ($e['status'] === 'en attente'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="validé">
                                <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">À confirmer</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="refusé">
                                <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">À refuser</button>
                            </form>
                        <?php elseif ($e['status'] === 'à comfirmé'): ?>
                            <span class="badge bg-info">En attente de validation admin</span>
                        <?php elseif ($e['status'] === 'à refusé'): ?>
                            <span class="badge bg-warning text-dark">En attente de validation</span>
                        <?php elseif ($e['status'] === 'Accepté'): ?>
                            <span class="badge bg-success">Événement validé</span>
                        <?php elseif ($e['status'] === 'Refusé'): ?>
                            <span class="badge bg-danger">Événement refusé</span>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-secondary inscriptions-btn ms-1" data-id="<?= $e['id'] ?>">Voir (<?= $e['inscrits'] ?? 0 ?>)</button>
                    </td>
                    <td><?= $e['inscrits'] ?? 0 ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Modifier Event -->
<div class="modal fade" id="editPopup" tabindex="-1" aria-labelledby="editEventLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEventLabel">Modifier l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="event_id" id="editevent_id">
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
        <button type="submit" class="btn btn-primary">Valider les changements</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Inscriptions -->
<div class="modal fade" id="inscriptionsPopup" tabindex="-1" aria-labelledby="inscriptionsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="inscriptionsLabel">Liste des inscrits</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div id="inscriptionsList">Chargement...</div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Popup Modifier event
    document.querySelectorAll('.modify-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('editevent_id').value = this.dataset.id;
            document.getElementById('editNbMax').value = row.children[4].textContent.trim();
            document.getElementById('editDate').value = row.children[2].textContent.trim();
            const editModal = new bootstrap.Modal(document.getElementById('editPopup'));
            editModal.show();
        });
    });

    // Popup Inscriptions
    document.querySelectorAll('.inscriptions-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const eventId = btn.dataset.id;
            const listDiv = document.getElementById('inscriptionsList');
            listDiv.innerHTML = "Chargement...";
            fetch(`/get_inscriptions?event_id=${eventId}`)
                .then(res => res.text())
                .then(html => listDiv.innerHTML = html);
            const inscriptionsModal = new bootstrap.Modal(document.getElementById('inscriptionsPopup'));
            inscriptionsModal.show();
        });
    });
</script>