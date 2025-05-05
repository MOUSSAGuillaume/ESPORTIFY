<?php
include_once("../db.php");
session_start();



// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: ../frontend/connexion.php");
    exit;
}

// Vérifie que l'utilisateur a le bon rôle
if ($_SESSION['user']['role'] !== 2) { // Par exemple, 2 pour Organisateur
    header("Location: ../frontend/accueil.php");
    exit;
}


// Génère un token CSRF s'il n'existe pas déjà
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['user']['pseudo'];

// Récupérer les actualités (ex: newsletters)
$newsQuery = mysqli_query($conn, "SELECT subject, message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 5");
$news = [];
while ($row = mysqli_fetch_assoc($newsQuery)) {
    $news[] = $row;
}

$eventsQuery = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");

if (!$eventsQuery) {
    die("Erreur lors de la récupération des événements: " . mysqli_error($conn));
}

if (mysqli_num_rows($eventsQuery) > 0) {
    // Traitement des événements
} else {
    echo "<p>Aucun événement à venir pour le moment.</p>";
}
// Vérifier si des événements ont été récupérés
if (mysqli_num_rows($eventsQuery) > 0) {
    // Affichage des événements
    echo "<ul class='events-list'>";
    while ($event = mysqli_fetch_assoc($eventsQuery)) {
        echo "<li class='event-item'>";
        echo "<strong>" . htmlspecialchars($event['title']) . "</strong><br>";
        echo "<em>" . htmlspecialchars($event['description']) . "</em><br>";
        echo "Date : " . date('d/m/Y', strtotime($event['event_date'])) . "<br>";
        echo "Statut : " . htmlspecialchars($event['status']) . "<br>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    // Aucun événement trouvé
    echo "<p>Aucun événement à venir pour le moment.</p>";
}

$id_organisateur = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC");
$stmt->bind_param("i", $id_organisateur);
$stmt->execute();
$events_result = $stmt->get_result();

$events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
}

$messageByInscription = [];

if (isset($_POST['action'], $_POST['inscription_id'], $_POST['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $inscription_id = (int) $_POST['inscription_id'];
        $action = $_POST['action'];
        $nouveau_statut = $action === 'accepter' ? 'accepte' : 'refuse';

        $stmt = mysqli_prepare($conn, "UPDATE inscriptions SET statut = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $nouveau_statut, $inscription_id);

        if (mysqli_stmt_execute($stmt)) {
            $messageByInscription[$inscription_id] = "<span style='color:green;'>✅ Statut mis à jour avec succès.</span>";
        } else {
            $messageByInscription[$inscription_id] = "<span style='color:red;'>❌ Erreur : " . mysqli_error($conn) . "</span>";
        }

        mysqli_stmt_close($stmt);
    } else {
        $messageByInscription[$_POST['inscription_id']] = "<span style='color:red;'>❌ Token CSRF invalide.</span>";
    }
}
if (isset($inscrit['statut'])) {
    $statut = htmlspecialchars($inscrit['statut']);
    $statusClass = match ($statut) {
        'accepte' => 'status-green',
        'refuse' => 'status-red',
        default => 'status-yellow',
    };
} else {
    $statut = 'non défini';
    $statusClass = 'status-unknown';  // Classe CSS pour un statut inconnu
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Esportify - Organisateur</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css">
</head>
<body>

<!-- Effet de console -->
<div class="console-overlay" id="console-overlay">
    <div class="console-text" id="console-text"></div>
</div>

<main class="hidden" id="dashboard-content">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="/frontend/organisateur_dashboard.php">
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                    </div>
                </a>
                <div class="semi-circle-outline"></div>
                <a href="/ESPORTIFY/backend/gestion_evenement.php" class="btn">📅 Gérer les événements</a>
            </div>

        </nav>
    </header>

    <section class="dashboard">
        <h1>Bienvenue Organisateur, <?php echo htmlspecialchars($username); ?> 🧩</h1>
        <div class="dashboard-links">
            <a href="/ESPORTIFY/frontend/organisateur_gestion.php" class="btn">Accéder à la gestion</a>
            <a href="/ESPORTIFY/backend/logout.php" class="btn btn-danger">Déconnexion</a>
            <a href="/ESPORTIFY/frontend/gestion-tournois.php" class="button">Gérer les tournois</a>
        </div>
    </section>

    <section class="actualites-section">
        <h2>📰 Fil d'actualité</h2>
        <?php if (!empty($news)) : ?>
            <ul class="news-list">
                <?php foreach ($news as $n) : ?>
                    <li class="news-item">
                        <strong><?php echo htmlspecialchars($n['subject']); ?></strong><br>
                        <span><?php echo nl2br(htmlspecialchars($n['message'])); ?></span><br>
                        <em>Publié le <?php echo date("d/m/Y H:i", strtotime($n['created_at'])); ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucune actualité pour le moment.</p>
        <?php endif; ?>
    </section>

    <section class="tournois-section">
        <h2>🏆 Événements en cours / à venir</h2>
        <?php if (!empty($tournois)) : ?>
            <ul class="tournois-list">
                <?php foreach ($tournois as $t) : ?>
                    <li class="tournoi-item">
                        <strong><?php echo htmlspecialchars($t['titre']); ?></strong> -
                        <?php echo date("d/m/Y", strtotime($t['date_tournoi'])); ?><br>
                        <em><?php echo htmlspecialchars($t['description']); ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucun tournoi prévu pour l’instant.</p>
        <?php endif; ?>
    </section>

    <?php foreach ($events as $event): ?>
  <h3><?= htmlspecialchars($event['title']) ?> (<?= date('Y/m/d', strtotime($event['event_date'])) ?>)</h3>

  <?php
  $event_id = $event['id'];
  $inscrits = mysqli_query($conn, "
    SELECT i.*, u.username FROM inscriptions i
    JOIN users u ON i.user_id = u.id
    WHERE i.event_id = $event_id
  ");
  ?>

  <?php if (mysqli_num_rows($inscrits) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Joueur</th>
          <th>Date d'inscription</th>
          <th>Statut</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php while ($inscrit = mysqli_fetch_assoc($inscrits)) : ?>
    <tr>
            <td><?= htmlspecialchars($inscrit['username']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($inscrit['date_inscription'])) ?></td>
        <td>
            <?php
                $statut = htmlspecialchars($inscrit['statut']);
                if ($statut === 'accepte') {
                    $emoji = '🟢';
                } elseif ($statut === 'refuse') {
                    $emoji = '🔴';
                } else {
                    $emoji = '🟡';
                }
                echo $emoji . " " . ucfirst($statut);
            ?>
        </td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="inscription_id" value="<?= $inscrit['id'] ?>">
                <input type="hidden" name="action" value="accepter">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit">✅</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="inscription_id" value="<?= $inscrit['id'] ?>">
                <input type="hidden" name="action" value="refuser">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit">❌</button>
            </form>

            <?php if (isset($messageByInscription[$inscrit['id']])) : ?>
                <div><?= $messageByInscription[$inscrit['id']] ?></div>
            <?php endif; ?>
        </td>
    </tr>

        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Aucun joueur inscrit.</p>
  <?php endif; ?>
<?php endforeach; ?>


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
        "Connexion au panel organisateur...",
        "Vérification de l'accès...",
        "Chargement des actualités et tournois...",
        "Bienvenue sur Esportify 🧩"
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
