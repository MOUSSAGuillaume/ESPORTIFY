<?php

include_once("../db.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 1) {
    header("Location: ../frontend/connexion.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Traitement du formulaire de modification + validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_and_validate') {
    $tournoiId = intval($_POST['tournoi_id']);
    $nbMax = intval($_POST['nb_max_participants']);
    $date = $_POST['date_event'];

    $stmt = $conn->prepare("UPDATE events SET nb_max_participants = ?, event_date = ?, status = 'validé', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $nbMax, $date, $tournoiId);
    if ($stmt->execute()) {
        header("Location: ../frontend/gestion_admin.php?success=Tournoi mis à jour et validé.");
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
    header("Location: ../frontend/gestion_admin.php?success=Tournoi supprimé avec succès.");
    exit;
}

// Validation / Refus d'un tournoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);
    $action = $_POST['action'];

    if ($action === 'valider') {
        $stmt = $conn->prepare("UPDATE events SET status = 'Accepté' WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        header("Location: ../frontend/gestion_admin.php?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE events SET status = 'Refusé' WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        header("Location: ../frontend/gestion_admin.php?success=Tournoi refusé.");
        exit;
    }
}


// Récupération des tournois avec le nombre d'inscrits
$tournois = $conn->query("SELECT e.*,
    (SELECT COUNT(*) FROM inscriptions i WHERE i.event_id = e.id) AS inscrits 
    FROM events e
    ORDER BY e.event_date DESC
");

// Tournoi à éditer
$editTournoi = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editTournoi = $result->fetch_assoc();
}

// Événements en attente
$evenements_en_attente = $conn->query("SELECT * FROM events WHERE status = 'en attente' ORDER BY created_at DESC");

// Newsletters
$newsletters = $conn->query("SELECT * FROM newsletters ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Admin</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css">
</head>
<body>

<div class="console-overlay" id="console-overlay">
  <div class="console-text" id="console-text"></div>
</div>

<main class="hidden" id="dashboard-content">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="/ESPORTIFY/frontend/admin_dashboard.php">
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

        <?= isset($_GET['success']) ? "<div class='msg success'>" . htmlspecialchars($_GET['success']) . "</div>" : '' ?>

        <h3>Tournois</h3>
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
                <?php while ($t = mysqli_fetch_assoc($tournois)): ?>
                <tr>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($t['event_date']))) ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                    <td><?= intval($t['nb_max_participants']) ?></td>
                    <td class="actions-cell">
                        <?php if ($t['status'] !== 'Accepté' && $t['status'] !== 'Refusé'): ?>
                            <button class="button modify-btn" data-id="<?= $t['id'] ?>">Modifier</button>
                        <?php endif; ?>
                        <a href="/ESPORTIFY/frontend/gestion_admin.php?delete=<?= $t['id'] ?>" class="button delete" onclick="return confirm('Supprimer ce tournoi ?')">Supprimer</a>
                        <?php if ($t['status'] === 'en attente'): ?>
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
                    </td>
                    <td>
                        <button class="button inscriptions-btn" data-id="<?= $t['id'] ?>">Voir (<?= $t['inscrits'] ?? 0 ?>)</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
        <h3>Newsletters publiées</h3>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Extrait</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($news = mysqli_fetch_assoc($newsletters)): ?>
                <tr>
                    <td><?= htmlspecialchars($news['title']) ?></td>
                    <td><?= htmlspecialchars($news['created_at']) ?></td>
                    <td><?= substr(htmlspecialchars($news['subject']), 0, 80) . '...' ?></td>
                    <td>
                        <button class="button toggle-news" data-id="<?= $news['id'] ?>">Voir</button>
                        <a href="/ESPORTIFY/frontend/gestion_newsletters.php?edit=<?= $news['id'] ?>" class="button">Modifier</a>
                    </td>
                </tr>
                <tr class="news-content-row" id="news-<?= $news['id'] ?>" style="display: none;">
                    <td colspan="4">
                        <div class="newsletter-full">
                            <?= nl2br(htmlspecialchars($news['message'])) ?>
                        </div>
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
        <h2>Modifier le tournoi</h2>
        <form id="editForm" method="POST">
            <input type="hidden" name="tournoi_id" id="editTournoiId">
            <label for="nb_max">Nombre de joueurs :</label>
            <input type="number" name="nb_max_participants" id="editNbMax" required>
            <label for="date">Date du tournoi :</label>
            <input type="date" name="date_event" id="editDate" required>
            <input type="hidden" name="action" value="update_and_validate">
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
const consoleText = document.getElementById("console-text");
const overlay = document.getElementById("console-overlay");
const dashboard = document.getElementById("dashboard-content");
const lines = ["Chargement du gestionnaire admin...", "Vérification des privilèges...", "Connexion validée ✔", "Interface admin prête !"];
let index = 0;
function typeLine() {
    if (index < lines.length) {
        consoleText.textContent += lines[index++] + "\n";
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
        fetch(`/ESPORTIFY/backend/get_inscriptions.php?event_id=${eventId}`)
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
        fetch(`/ESPORTIFY/backend/update_inscription_status.php`, {
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

        fetch(`/ESPORTIFY/backend/get_inscriptions.php?event_id=${eventId}`)
            .then(res => res.text())
            .then(html => listDiv.innerHTML = html);
        popup.style.display = 'flex';
    });
});

document.querySelector('.close-inscriptions').onclick = () => document.getElementById('inscriptionsPopup').style.display = 'none';


</script>

</body>
</html>

