<?php
include_once("../db.php");
session_start();


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Organisateur') {
    header("Location: /ESPORTIFY/frontend/connexion.php");
    exit;
}

$username = $_SESSION['user']['pseudo'];
$user_id = $_SESSION['user']['id'];

// Init messages
$msg_tournoi = "";
$msg_newsletter = "";
$msg_evenement = "";
$msg_proposition = ""; // Nouveau message pour propositions validées

// ========== GESTION DES PROPOSITIONS D'ÉVÉNEMENTS JOUEURS ==========

if (isset($_GET['valider_proposition'])) {
    $id_prop = (int)$_GET['valider_proposition'];

    // Récupérer les données de la proposition
    $result = mysqli_query($conn, "SELECT * FROM evenements_proposes WHERE id = $id_prop");
    $proposition = mysqli_fetch_assoc($result);

    if ($proposition && $proposition['status'] === 'En attente') {
        // Échapper les valeurs
        $titre = mysqli_real_escape_string($conn, $proposition['titre']);
        $description = mysqli_real_escape_string($conn, $proposition['description']);
        $date = $proposition['event_date'];

        // Insérer dans les événements
        $insert = mysqli_query($conn, "INSERT INTO evenements (title, description, event_date, status, created_by, created_at)
                                       VALUES ('$titre', '$description', '$date', 'À venir', $user_id, NOW())");

        if ($insert) {
            mysqli_query($conn, "UPDATE evenements_proposes SET status = 'Validé par orga' WHERE id = $id_prop");
            $msg_proposition = "✅ Proposition validée et ajoutée aux événements.";
        } else {
            $msg_proposition = "❌ Erreur lors de l'import dans les événements.";
        }
    }
}

if (isset($_GET['rejeter_proposition'])) {
    $id_prop = (int)$_GET['rejeter_proposition'];
    mysqli_query($conn, "UPDATE evenements_proposes SET status = 'Rejeté' WHERE id = $id_prop");
    $msg_proposition = "❌ Proposition rejetée.";
}

// Récupérer toutes les propositions
$propositions = mysqli_query($conn, "SELECT ep.*, u.pseudo AS joueur
                                     FROM evenements_proposes ep
                                     JOIN users u ON ep.propose_par = u.id
                                     ORDER BY ep.created_at DESC");


// ========== GESTION TOURNOIS ==========
if (isset($_POST['submit_tournament'])) {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = $_POST['date_tournoi'];

    $sql = "INSERT INTO tournois (titre, description, date_tournoi)
            VALUES ('$titre', '$description', '$date')";
    mysqli_query($conn, $sql);
}

if (isset($_GET['delete_tournoi'])) {
    $id = (int)$_GET['delete_tournoi'];
    mysqli_query($conn, "DELETE FROM tournois WHERE id = $id");
    $msg_tournoi = "❌ Tournoi supprimé.";
}

// ========== GESTION NEWSLETTER ==========
if (isset($_POST['submit_newsletter'])) {
    $sujet = mysqli_real_escape_string($conn, $_POST['subject']);
    $contenu = mysqli_real_escape_string($conn, $_POST['message']);

    $sql_newsletter = "INSERT INTO newsletters (sujet, contenu) VALUES ('$sujet', '$contenu')";
    if (mysqli_query($conn, $sql_newsletter)) {
        $msg_newsletter = "✅ Newsletter publiée avec succès !";
    } else {
        $msg_newsletter = "❌ Erreur lors de la publication : " . mysqli_error($conn);
    }
}

// ========== GESTION EVENEMENTS ==========
$event = null;

if (isset($_POST['submit_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = $_POST['event_date'];
    $status = $_POST['status'];

    $sql = "INSERT INTO evenements (title, description, event_date, status, created_by, created_at)
            VALUES ('$title', '$description', '$event_date', '$status', '$user_id', NOW())";

    if (mysqli_query($conn, $sql)) {
        $msg_evenement = "✅ Événement ajouté avec succès.";
    } else {
        $msg_evenement = "❌ Erreur lors de l'ajout.";
    }
}

if (isset($_GET['delete_event'])) {
    $id = (int)$_GET['delete_event'];
    mysqli_query($conn, "DELETE FROM evenements WHERE id = $id");
    $msg_evenement = "🗑️ Événement supprimé.";
}

if (isset($_GET['edit_event'])) {
    $id = (int)$_GET['edit_event'];
    $result = mysqli_query($conn, "SELECT * FROM evenements WHERE id = $id");
    $event = mysqli_fetch_assoc($result);

    if (isset($_POST['update_event'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $event_date = $_POST['event_date'];
        $status = $_POST['status'];

        $update_sql = "UPDATE evenements SET
            title = '$title',
            description = '$description',
            event_date = '$event_date',
            status = '$status',
            updated_at = NOW()
            WHERE id = $id";

        if (mysqli_query($conn, $update_sql)) {
            $msg_evenement = "✅ Événement mis à jour.";
            $event = null; // Reset form
        } else {
            $msg_evenement = "❌ Erreur lors de la mise à jour.";
        }
    }
}

// Requêtes d'affichage
$tournois = mysqli_query($conn, "SELECT * FROM tournois ORDER BY date_tournoi DESC");
$evenements = mysqli_query($conn, "SELECT * FROM evenements ORDER BY event_date DESC");

$participantsData = [];
$results = mysqli_query($conn, "SELECT t.id, t.titre, u.pseudo
                                FROM participants p
                                JOIN tournois t ON p.id_tournoi = t.id
                                JOIN users u ON p.id_joueur = u.id
                                ORDER BY t.id");
while ($row = mysqli_fetch_assoc($results)) {
    $participantsData[$row['id']]['titre'] = $row['titre'];
    $participantsData[$row['id']]['joueurs'][] = $row['pseudo'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panel Organisateur - Esportify</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css">
    <style>
        .tab-content { display: none; padding: 1em; }
        .tab-content.active { display: block; }
        .tabs button { margin: 0 5px; padding: 0.5em 1em; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background-color: #eee; }
        .msg { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<main>
    <header>
        <h1>🎮 Panel organisateur - <?= htmlspecialchars($username) ?></h1>
        <nav class="tabs">
            <button onclick="showTab('tournois')">⚙️ Tournois</button>
            <button onclick="showTab('participants')">👥 Participants</button>
            <button onclick="showTab('evenements')">📅 Événements</button>
            <button onclick="showTab('resultats')">📊 Résultats</button>
            <button onclick="showTab('newsletter')">✉️ Newsletter</button>
        </nav>
    </header>

    <!-- TOURNOIS -->
    <section id="tournois" class="tab-content active">
        <h2>Créer un tournoi</h2>
        <form method="POST">
            <label>Titre : <input type="text" name="titre" required></label><br>
            <label>Description : <textarea name="description" required></textarea></label><br>
            <label>Date : <input type="date" name="date_tournoi" required></label><br>
            <button name="submit_tournament">Créer</button>
        </form>
        <?php if (!empty($msg_tournoi)) echo "<p class='msg'>$msg_tournoi</p>"; ?>

        <h3>Tournois existants :</h3>
        <ul>
        <?php while ($t = mysqli_fetch_assoc($tournois)) : ?>
            <li>
                <strong><?= htmlspecialchars($t['titre']) ?></strong> - <?= $t['date_tournoi'] ?>
                <a href="?delete_tournoi=<?= $t['id'] ?>" onclick="return confirm('Supprimer ce tournoi ?')" style="color:red;">Supprimer</a>
            </li>
        <?php endwhile; ?>
        </ul>
    </section>

    <!-- PARTICIPANTS -->
    <section id="participants" class="tab-content">
        <h2>Liste des participants</h2>
        <?php foreach ($participantsData as $id => $data) : ?>
            <div>
                <h4><?= htmlspecialchars($data['titre']) ?></h4>
                <ul>
                    <?php foreach ($data['joueurs'] as $joueur) : ?>
                        <li><?= htmlspecialchars($joueur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- EVENEMENTS -->
    <section id="evenements" class="tab-content">
        <h2>Gérer les événements</h2>
        <?= !empty($msg_evenement) ? "<p class='msg'>$msg_evenement</p>" : '' ?>

        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($e = mysqli_fetch_assoc($evenements)) : ?>
                <tr>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= $e['event_date'] ?></td>
                    <td><?= $e['status'] ?></td>
                    <td>
                        <a href="?edit_event=<?= $e['id'] ?>">✏️</a>
                        <a href="?delete_event=<?= $e['id'] ?>" onclick="return confirm('Supprimer cet événement ?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Formulaire d'ajout / modification d'événement -->
        <h3><?= $event ? "Modifier l'événement" : "Ajouter un événement" ?></h3>
        <form method="POST">
            <label>Titre : <input type="text" name="title" value="<?= $event['title'] ?? '' ?>" required></label><br><br>
            <label>Description : <textarea name="description" required><?= $event['description'] ?? '' ?></textarea></label><br><br>
            <label>Date : <input type="date" name="event_date" value="<?= $event['event_date'] ?? '' ?>" required></label><br><br>
            <label>Statut :
                <select name="status" required>
                    <option value="À venir" <?= ($event['status'] ?? '') == 'À venir' ? 'selected' : '' ?>>À venir</option>
                    <option value="Terminé" <?= ($event['status'] ?? '') == 'Terminé' ? 'selected' : '' ?>>Terminé</option>
                    <option value="Annulé" <?= ($event['status'] ?? '') == 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </label><br><br>
            <?php if ($event): ?>
                <button name="update_event">💾 Mettre à jour</button>
            <?php else: ?>
                <button name="submit_event">➕ Ajouter</button>
            <?php endif; ?>
        </form>
    </section>

    <section id="propositions" class="tab-content">
    <h2>📨 Événements proposés par les joueurs</h2>
    <?= !empty($msg_proposition) ? "<p class='msg'>$msg_proposition</p>" : "" ?>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Date</th>
                <th>Proposé par</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = mysqli_fetch_assoc($propositions)) : ?>
            <tr>
                <td><?= htmlspecialchars($p['titre']) ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
                <td><?= $p['event_date'] ?></td>
                <td><?= htmlspecialchars($p['joueur']) ?></td>
                <td><?= $p['status'] ?></td>
                <td>
                    <?php if ($p['status'] === 'En attente') : ?>
                        <a href="?valider_proposition=<?= $p['id'] ?>">✅ Valider</a>
                        <a href="?rejeter_proposition=<?= $p['id'] ?>" style="color:red;" onclick="return confirm('Rejeter cette proposition ?')">❌ Rejeter</a>
                    <?php else: ?>
                        <em>Action effectuée</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</section>

    <!-- RESULTATS -->
    <section id="resultats" class="tab-content">
        <h2>Résultats</h2>
        <p>📌 À compléter avec le système de scores.</p>
        <table>
            <thead><tr><th>Tournoi</th><th>Gagnant</th><th>Score</th></tr></thead>
            <tbody><tr><td colspan="3">Aucun résultat enregistré.</td></tr></tbody>
        </table>
    </section>

    <!-- NEWSLETTER -->
    <section id="newsletter" class="tab-content">
        <h2>Publier une Newsletter</h2>
        <form method="POST">
            <label>Sujet : <input type="text" name="subject" required></label><br><br>
            <label>Message : <textarea name="message" required></textarea></label><br><br>
            <button name="submit_newsletter">Publier</button>
        </form>
        <?php if (!empty($msg_newsletter)) echo "<p class='msg'>$msg_newsletter</p>"; ?>
    </section>

    <footer><p>© Esportify - Tous droits réservés</p></footer>
</main>

<script>
    function showTab(id) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.getElementById(id).classList.add('active');
    }
</script>
</body>
</html>
