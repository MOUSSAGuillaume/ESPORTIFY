<?php
include_once("../db.php");
session_start();

// Vérifier que l'utilisateur est un organisateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 2) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php");
    exit;
}

$username = $_SESSION['user']['pseudo'];
$user_id = $_SESSION['user']['id'];

// ========== GESTION DES EVENEMENTS ==========
// Message d'événement
$msg_evenement = "";

// Ajouter un événement
if (isset($_POST['submit_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = $_POST['event_date'];
    $status = 'en attente'; // statut par défaut
    $nb_max_participants = 0; // valeur temporaire, sera modifiée par admin plus tard

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, status, created_by, created_at, nb_max_participants)
                            VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    if ($stmt) {
        $stmt->bind_param("ssssii", $title, $description, $event_date, $status, $user_id, $nb_max_participants);
        if ($stmt->execute()) {
            $msg_evenement = "✅ Événement ajouté avec succès.";
        } else {
            $msg_evenement = "❌ Erreur lors de l'ajout : " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg_evenement = "❌ Erreur de préparation de la requête.";
    }
}

// Traitement du formulaire de modification + validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_event') {
    $event_id = intval($_POST['event_id']);
    $nbMax = intval($_POST['nb_max_participants']);
    $date = $_POST['date_event'];

    $stmt = $conn->prepare("UPDATE events SET nb_max_participants = ?, event_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $nbMax, $date, $event_id);
    if ($stmt->execute()) {
        header("Location: https://esportify.alwaysdata.net/frontend/organisateur_gestion.php?success=Tournoi mis à jour et validé.");
        exit;
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Validation / Refus d'un tournoi
if (isset($_POST['action']) && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $action = $_POST['action'];
    if ($action === 'validé') {
        $stmt = $conn->prepare("UPDATE events SET status = 'à confirmer' WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        header("Location: https://esportify.alwaysdata.net/frontend/organisateur_gestion.php?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refusé') {
        $stmt = $conn->prepare("UPDATE events SET status = 'à refuser' WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        header("Location: https://esportify.alwaysdata.net/frontend/organisateur_gestion.php?success=Tournoi refusé.");
        exit;
    }
}

// Récupération des événements avec le nombre d'inscrits
$events = $conn->query("SELECT e.*,
    (SELECT COUNT(*) FROM inscriptions i WHERE i.event_id = e.id) AS inscrits
    FROM events e
    ORDER BY e.event_date DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Organisateur - Esportify</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/dashboard.css">
</head>
<body>

<main id="dashboard-content">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="https://esportify.alwaysdata.net/frontend/organisateur_dashboard.php">
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                    </div>
                </a>
                <div class="semi-circle-outline"></div>
            </div>
        </nav>
    </header>

    <!-- EVENEMENTS -->
    <section id="evenements" class="tab-content active">
        <h1>Gestion Organisateur</h1>
        <?= isset($_GET['success']) ? "<div class='msg success'>" . htmlspecialchars($_GET['success']) . "</div>" : '' ?>
        <div class="dashboard-links">
            <a href="https://esportify.alwaysdata.net/frontend/organisateur_gestion.php" class="btn">Gestion des Events</a>
            <!--<a href="/ESPORTIFY/frontend/gestion_utilisateurs.php" class="btn">Gérer les utilisateurs</a>-->
            <a href="https://esportify.alwaysdata.net/frontend/gestion_newsletters.php" class="btn">Gestion des newsletters</a>
            <a href="https://esportify.alwaysdata.net/backend/logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </section>
        <h3>Événements</h3>
        <!-- Table des événements -->
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
            <?php while ($e = mysqli_fetch_assoc($events)) : ?>
                <tr>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($e['event_date']))) ?></td>
                    <td><?= htmlspecialchars($e['status']) ?></td>
                    <td><?= intval($e['nb_max_participants']) ?></td>
                    <td class="actions-cell">
                        <?php if ($e['status'] !== 'accepté' && $e['status'] !== 'refusé'): ?>
                            <button class="button modify-btn" data-id="<?= $e['id'] ?>">Modifier</button>
                        <?php endif; ?>
                        
                        <!-- Validation et Refus -->
                        <?php if ($e['status'] === 'en attente'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="validé">
                                <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                <button type="submit" class="button">à confirmé</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="refusé">
                                <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                <button type="submit" class="button delete">à refusé</button>
                            </form>
                        <?php elseif ($e['status'] === 'à comfirmé'): ?>
                            <span>En attente de validation admin</span>
                        <?php elseif ($e['status'] === 'à refusé'): ?>
                            <span>En attente de validation</span>
                        <?php elseif ($e['status'] === 'Accepté'): ?>
                            <span>Événement validé</span>
                        <?php elseif ($e['status'] === 'Refusé'): ?>
                            <span>Événement refusé</span>
                        <?php endif; ?>
                        
                        <button class="button inscriptions-btn" data-id="<?= $e['id'] ?>">Voir (<?= $e['inscrits'] ?? 0 ?>)</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </section>

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
            <input type="hidden" name="event_id" id="editevent_id">
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
    // Gérer l'affichage du popup de modification
    document.querySelectorAll('.modify-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = this.dataset.id;
            const date = row.children[2].textContent.trim();
            const nbMax = row.children[4].textContent.trim();
            document.getElementById('editevent_id').value = id;
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

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('valider-inscription') || e.target.classList.contains('refuser-inscription')) {
            const id = e.target.dataset.id;
            const action = e.target.classList.contains('valider-inscription') ? 'confirmé' : 'refusé';

            fetch('https://esportify.alwaysdata.net/backend/update_inscription_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `inscription_id=${id}&action=${action}`
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);

            // Recharge la liste des inscrits sans recharger la page
            const popup = document.getElementById('inscriptionsPopup');
            const currentEventId = document.querySelector('.inscriptions-btn.opened')?.dataset.id;

            if (popup && currentEventId) {
                fetch(`https://esportify.alwaysdata.net/backend/get_inscriptions.php?event_id=${currentEventId}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('inscriptionsList').innerHTML = html;
                    });
            }
        })
        .catch(error => alert("Erreur AJAX : " + error));
    }
});
</script>

</body>
</html>
