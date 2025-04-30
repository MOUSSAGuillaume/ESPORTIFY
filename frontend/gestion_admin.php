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
mysqli_query($conn, "UPDATE users SET last_activity = NOW() WHERE id = $userId");

// Ajout / modification d'un tournoi
if (isset($_POST['save_tournoi'])) {  // ici c'était save_event => corrigé
    $id = isset($_POST['tournoi_id']) ? intval($_POST['tournoi_id']) : null; // pas event_id
    $nom = mysqli_real_escape_string($conn, $_POST['titre']); // pas title
    $jeu = mysqli_real_escape_string($conn, $_POST['jeu']); // ajouté jeu
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = $_POST['date_event'];
    $nb_max = intval($_POST['nb_max_participants']);
    $statut = 'en attente';

    if ($id) {
        $sql = "UPDATE events SET title='$nom', jeu='$jeu', description='$description', event_date='$date', nb_max_participants=$nb_max, updated_at=NOW() WHERE id = $id";
        $msg = "Tournoi modifié avec succès.";
    } else {
        $sql = "INSERT INTO events (title, jeu, description, event_date, status, nb_max_participants, created_by)
                VALUES ('$nom', '$jeu', '$description', '$date', '$statut', $nb_max, $userId)";
        $msg = "Tournoi ajouté avec succès.";
    }

    mysqli_query($conn, $sql);
    header("Location: ../frontend/gestion_admin.php?success=$msg");
    exit;
}


// Suppression d'un tournoi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM events WHERE id = $id");
    header("Location: ../frontend/gestion_admin.php?success=Tournoi supprimé avec succès.");
    exit;
}

// Validation / Refus d'un tournoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);
    $action = $_POST['action'];

    if ($action === 'valider') {
        mysqli_query($conn, "UPDATE events SET status='validé' WHERE id = $eventId");
        header("Location: /frontend/gestion_admin.php?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refuser') {
        mysqli_query($conn, "UPDATE events SET status='refusé' WHERE id = $eventId");
        header("Location: ../frontend/gestion_admin.php?success=Tournoi refusé.");
        exit;
    }
}

// Récupérations des tournois
$tournois = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date DESC");

// Récupération des événements en attente
$evenements_en_attente = mysqli_query($conn, "SELECT * FROM events WHERE status = 'en attente' ORDER BY created_at DESC");

// Récupérer les newsletters
$newsletters = mysqli_query($conn, "SELECT * FROM newsletters ORDER BY created_at DESC");

$editTournoi = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM events WHERE id = $editId");
    $editTournoi = mysqli_fetch_assoc($res);
}
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

        <!-- Gestion des Tournois -->
        <div class="form-wrapper">
            <h2><?= $editTournoi ? 'Modifier event': 'Ajouter un tournoi' ?></h2>
            <form method="POST" action="/ESPORTIFY/backend/gestion_evenements.php">
                <?php if ($editTournoi): ?>
                    <input type="hidden" name="tournoi_id" value="<?= $editTournoi['id'] ?>">
                <?php endif; ?>
                <input type="text" name="titre" placeholder="Nom du tournoi" required value="<?= htmlspecialchars($editTournoi['title'] ?? '') ?>">
                <input type="text" name="jeu" placeholder="Nom du jeu" required value="<?= htmlspecialchars($editTournoi['jeu'] ?? '') ?>">
                <textarea name="description" placeholder="Description"><?= htmlspecialchars($editTournoi['description'] ?? '') ?></textarea>
                <input type="date" name="date_event" required value="<?= htmlspecialchars($editTournoi['event_date'] ?? '') ?>">
                <input type="number" name="nb_max_participants" placeholder="Nb max de joueurs" required min="2" value="<?= intval($editTournoi['nb_max_participants'] ?? 8) ?>">
                <button type="submit" name="save_tournoi" class="button"><?= $editTournoi ? 'Modifier' : 'Ajouter' ?></button>
            </form>
        </div>ss

        <!-- Table des Tournois -->
        <h3>Tournois</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Jeu</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Places</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($t = mysqli_fetch_assoc($tournois)): ?>
                <tr>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars(date('Y/m/d', strtotime($t['event_date']))) ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                    <td><?= intval($t['nb_max_participants']) ?></td>
                    <td>
                        <a href="/ESPORTIFY/frontend/gestion_admin.php?edit=<?= $t['id'] ?>" class="button">Modifier</a>
                        <a href="/ESPORTIFY/frontend/gestion_admin.php?view=<?= $t['id'] ?>" class="button">Ajouter</a>
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

                            <?php
                            // Débogage - Vérification des données envoyées via POST
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                error_log("Données POST reçues: " . print_r($_POST, true)); // Affiche les données envoyées en POST
                            }
                            ?>

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Gestion des Événements -->
        <h3><a href="/ESPORTIFY/backend/gestion_evenements.php" class="btn">Gérer les Événements en Attente de Validation</a></h3>

        <!-- Gestion des Newsletters -->
        <h3>Newsletters publiées</h3>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date de Publication</th>
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
                            <?= nl2br(htmlspecialchars($news['contenu'])) ?>
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

<script>
    const consoleText = document.getElementById("console-text");
    const overlay = document.getElementById("console-overlay");
    const dashboard = document.getElementById("dashboard-content");

    const lines = [
        "Chargement du gestionnaire admin...",
        "Vérification des privilèges...",
        "Connexion validée ✔",
        "Interface admin prête !"
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

    // Script pour les newsletters
    document.querySelectorAll('.toggle-news').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const contentRow = document.getElementById('news-' + id);
            if (contentRow.style.display === 'none') {
                contentRow.style.display = 'table-row';
                this.textContent = 'Cacher';
            } else {
                contentRow.style.display = 'none';
                this.textContent = 'Voir';
            }
        });
    });
</script>

</body>
</html>
