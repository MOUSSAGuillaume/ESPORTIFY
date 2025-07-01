<?php
if (empty($_SESSION['user'])) {
  header('Location: /index.php?page=connexion');
  exit;
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once(__DIR__ . '/../db.php');
include_once(__DIR__ . "/../backend/event_handler.php");
include_once(__DIR__ . "/../backend/comment_handler.php");
include_once(__DIR__ . "/../backend/like_handler.php");
include_once(__DIR__ . "/../backend/auth_check.php");


$username = $_SESSION['user']['pseudo'];
$id_joueur = $_SESSION['user']['id'];

// Récupération des événements
$evenements = [];
$resultEvents = mysqli_query($conn, "SELECT * FROM events WHERE status = 'accepté' ORDER BY event_date DESC");
if ($resultEvents) {
  while ($event = mysqli_fetch_assoc($resultEvents)) {
    $evenements[] = $event;
  }
}
?>

<link rel="stylesheet" href="../css/dashboard.css">

<!-- Effet console -->
<div class="console-overlay" id="console-overlay">
  <div class="console-text" id="console-text"></div>
</div>

<main id="dashboard-content" class="container my-4 d-none">
  <div class="row mb-4">
    <div class="col-12 text-center">
      <h1 class="fw-bold">Bienvenue, <?= htmlspecialchars($username); ?> 🎮</h1>
    </div>
  </div>

  <!-- Evénements proposés -->
  <div class="row mb-5">
    <div class="col-12">
      <h2 class="h4 mb-3">📅 Événements proposés</h2>
      <button id="eventButton" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#eventModal">Proposer un événement</button>
      <?php if (!empty($message)) {
        echo '<div class="alert alert-info">' . $message . '</div>';
      } ?>
      <?php if (empty($evenements)) : ?>
        <p>Aucun événement proposé pour le moment.</p>
      <?php else : ?>
        <div class="table-responsive">
          <table class="table table-dark table-hover event-table align-middle">
            <thead>
              <tr>
                <th>Titre du jeu</th>
                <th>Nombre de joueurs</th>
                <th>Date</th>
                <th>Nombre d'inscrits</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($evenements as $event) : ?>
                <tr>
                  <td><?= htmlspecialchars($event['title']) ?></td>
                  <td><?= (int)$event['nb_max_participants'] ?> joueur(s)</td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?></td>
                  <td>
                    <?php
                    $id_event = $event['id'];
                    $resCount = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM inscriptions WHERE event_id = ?");
                    mysqli_stmt_bind_param($resCount, "i", $id_event);
                    mysqli_stmt_execute($resCount);
                    $result = mysqli_stmt_get_result($resCount);
                    $count = mysqli_fetch_assoc($result)['total'];
                    echo $count . " joueur(s)";
                    ?>
                  </td>
                  <td>
                    <?php
                    $stmt = mysqli_prepare($conn, "SELECT * FROM inscriptions WHERE event_id = ? AND user_id = ?");
                    mysqli_stmt_bind_param($stmt, "ii", $id_event, $id_joueur);
                    mysqli_stmt_execute($stmt);
                    $check = mysqli_stmt_get_result($stmt);
                    $est_inscrit = mysqli_num_rows($check) > 0;
                    ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                      <?php if ($est_inscrit): ?>
                        <button type="submit" name="desinscription_event" class="btn btn-warning btn-sm mb-1">Se désinscrire</button>
                      <?php else: ?>
                        <button type="submit" name="inscription_event" class="btn btn-info btn-sm mb-1">S’inscrire</button>
                      <?php endif; ?>
                    </form>
                    <button type="button" onclick="showDescription(<?= htmlspecialchars(json_encode($event['id'])) ?>)" class="btn btn-outline-light btn-sm mb-1">Description</button>
                    <div id="desc-<?= $event['id'] ?>" style="display:none;"><?= htmlspecialchars($event['description']) ?></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Fil d’actualités -->
  <div class="row">
    <div class="col-12">
      <h2 class="h4 mb-3">📰 Fil d’actualités</h2>
      <?php
      $sql = "SELECT n.id, n.subject, n.message, n.created_at, u.username AS author
      FROM newsletters n
      JOIN users u ON n.created_by = u.id
      ORDER BY n.created_at DESC";
      $result = mysqli_query($conn, $sql);
      while ($row = mysqli_fetch_assoc($result)) {
        $id_actu = $row['id'];
        echo "<div class='news-item'>";
        echo "<h5>" . htmlspecialchars($row['subject']) . "</h5>";
        echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>";
        echo "<small>Publié par " . htmlspecialchars($row['author']) . " le " . date('d/m/Y H:i', strtotime($row['created_at'])) . "</small>";

        // Formulaire commentaire
        echo "<form method='POST' action='' class='mt-2'>";
        echo "<input type='hidden' name='id_newsletter' value='" . $id_actu . "' />";
        echo "<textarea name='commentaire' class='form-control form-control-sm mb-1' placeholder='Écris un commentaire...' required></textarea>";
        echo "<button type='submit' name='poster_commentaire' class='btn btn-primary btn-sm'>Commenter</button>";
        echo "</form>";

        // Affichage des commentaires
        $comments = [];
        $query = "SELECT cn.*, u.username FROM commentaires_newsletters cn
                    JOIN users u ON cn.id_user = u.id
                    WHERE cn.id_newsletter = $id_actu
                    ORDER BY cn.date_commentaire DESC";
        $resCom = mysqli_query($conn, $query);
        while ($com = mysqli_fetch_assoc($resCom)) {
          $comments[] = $com;
        }

        foreach ($comments as $com) {
          echo "<div class='commentaire'>";
          echo "<strong>" . htmlspecialchars($com['username']) . " :</strong> ";
          echo "<span>" . nl2br(htmlspecialchars($com['commentaire'])) . "</span>";
          echo "<small class='text-secondary'> — " . date('d/m/Y H:i', strtotime($com['date_commentaire'])) . "</small>";

          // Réponses
          $id_commentaire = $com['id'];
          $repQuery = "SELECT rc.*, u.username FROM reponses_commentaires rc
                           JOIN users u ON rc.id_joueur = u.id
                           WHERE rc.id_commentaire = $id_commentaire
                           ORDER BY date_reponse DESC";
          $reponses = mysqli_query($conn, $repQuery);

          while ($rep = mysqli_fetch_assoc($reponses)) {
            echo "<div class='reponse'>";
            echo "<strong>" . htmlspecialchars($rep['username']) . " (réponse) :</strong> ";
            echo "<span>" . nl2br(htmlspecialchars($rep['reponse'])) . "</span>";
            echo "<small class='text-secondary'> — " . date('d/m/Y H:i', strtotime($rep['date_reponse'])) . "</small>";
            echo "</div>";
          }

          // Formulaire de réponse
          echo "<form method='POST' class='mt-1'>";
          echo "<input type='hidden' name='id_commentaire' value='" . $com['id'] . "' />";
          echo "<textarea name='reponse' class='form-control form-control-sm mb-1' placeholder='Répondre à ce commentaire...' required></textarea>";
          echo "<button type='submit' name='poster_reponse' class='btn btn-outline-primary btn-sm'>Répondre</button>";
          echo "</form>";
          echo "</div>"; // fin commentaire
        }

        // Like system
        $likeCheckQuery = "SELECT * FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur";
        $likeCheck = mysqli_query($conn, $likeCheckQuery);
        $a_liker = mysqli_num_rows($likeCheck) > 0;

        $countLikesQuery = "SELECT COUNT(*) as total FROM likes_actualites WHERE id_actualite = $id_actu";
        $countLikes = mysqli_fetch_assoc(mysqli_query($conn, $countLikesQuery))['total'];

        echo "<form method='POST' class='mt-1'>";
        echo "<input type='hidden' name='id_actualite_like' value='$id_actu'>";
        echo "<button type='submit' name='like_actualite' class='btn btn-link btn-sm'>";
        echo $a_liker ? "❤️ " : "🤍 ";
        echo "$countLikes Like(s)";
        echo "</button>";
        echo "</form>";

        echo "</div>"; // .news-item
      }
      ?>
    </div>
  </div>
</main>

<!-- Modal proposer un événement (Bootstrap modal) -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="eventModalLabel">Proposer un événement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre du jeu</label>
            <input type="text" name="titre" id="titre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
          </div>
          <div class="mb-3">
            <label for="date_event" class="form-label">Date</label>
            <input type="date" name="date_event" id="date_event" class="form-control" required>
          </div>
          <?php if (!empty($message)) {
            echo '<div class="message-popup">' . $message . '</div>';
          } ?>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit" class="btn btn-success">Ajouter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Description Modal -->
<div class="modal fade" id="descPopup" tabindex="-1" aria-labelledby="descPopupLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="descPopupLabel">Description de l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p id="eventDescriptionText"></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Effet console
  const consoleText = document.getElementById("console-text");
  const overlay = document.getElementById("console-overlay");
  const dashboard = document.getElementById("dashboard-content");

  const lines = [
    "Connexion au profil joueur...",
    "Chargement des statistiques...",
    "Synchronisation avec les tournois...",
    "Bienvenue sur ton espace Esportify ✔"
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
        dashboard.classList.remove("d-none");
      }, 1000);
    }
  }
  typeLine();

  // Voir description event (Bootstrap modal)
  window.showDescription = function(eventId) {
    const desc = document.getElementById("desc-" + eventId).innerText;
    document.getElementById("eventDescriptionText").textContent = desc;
    const modal = new bootstrap.Modal(document.getElementById('descPopup'));
    modal.show();
  };
</script>