<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
set_exception_handler(function($e) {
  echo "<pre>Erreur critique : " . $e->getMessage() . "</pre>";
});

include_once ("../db.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include_once ("../backend/event_handler.php");// Gestion des événements
include_once ("../backend/comment_handler.php");// Gestion des commentaires
include_once ("../backend/like_handler.php");// Gestion des likes
include_once ("../backend/auth_check.php");// Vérification de l'authentification

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

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Esportify - Espace Joueur</title>
  <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css" />
</head>
<body>
  <!-- Effet console -->
  <div class="console-overlay" id="console-overlay">
    <div class="console-text" id="console-text"></div>
</div>


  <main id="dashboard-content">
    <header>
      <nav class="custom-navbar">
        <div class="logo-wrapper">
          <a href="/ESPORTIFY/frontend/joueur_dashboard.php">
            <div class="logo-container">
              <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
            </div>
          </a>
          <div class="semi-circle-outline"></div>
        </div>
      </nav>
    </header>

    <section class="dashboard">
      <h1>Bienvenue, <?php echo htmlspecialchars($username); ?> 🎮</h1>
      <div class="dashboard-links">
        <a href="http://localhost/ESPORTIFY/frontend/profile.php" class="btn">Mon profil</a>
        <button id="eventButton" class="btn">Proposer un événement</button>
        <a href="/ESPORTIFY/backend/logout.php" class="btn btn-danger">Se déconnecter</a>

      </div>
    </section>

    <section class="dashboard">
      <h2>📅 Événements proposés</h2>
      <?php if (!empty($message)) { echo $message; } ?>
      <?php if (empty($evenements)) : ?>
        <p>Aucun événement proposé pour le moment.</p>
      <?php else : ?>
        <table class="event-table">
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
                <!-- Affiche le nb de joueurs requis -->
                <td><?= (int)$event['nb_max_participants'] ?> joueur(s)</td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?></td>
                <td>
                  <?php
                  // Récupérer le nombre d'inscriptions pour l'événement
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
                  // Vérifie si l'utilisateur est déjà inscrit
                  $stmt = mysqli_prepare($conn, "SELECT * FROM inscriptions WHERE event_id = ? AND user_id = ?");
                  mysqli_stmt_bind_param($stmt, "ii", $id_event, $id_joueur);
                  mysqli_stmt_execute($stmt);$check = mysqli_stmt_get_result($stmt); // récupération du résultat icis
                  $est_inscrit = mysqli_num_rows($check) > 0;
                  ?>
  
                  <?php if ($est_inscrit): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit" name="desinscription_event">Se désinscrire</button>
                  </form>
                  <?php else: ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit" name="inscription_event">S’inscrire</button>
                  </form>
                  <?php endif; ?>

                  <button type="button" onclick="showDescription(<?= htmlspecialchars(json_encode($event['id'])) ?>)">Voir la description</button>
                  <?php
                  // Ajoute la description dans un attribut data pour l'utiliser plus tard
                  echo "<div id='desc-" . $event['id'] . "' style='display:none;'>"
                  . htmlspecialchars($event['description']) .
                  "</div>";
                  ?>
                </td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section class="news-feed">
      <h2>📰 Fil d’actualités</h2>
      <?php
      // Affichage des newsletters
      $sql = "SELECT n.id, n.subject, n.message, n.created_at, u.username AS author
      FROM newsletters n
      JOIN users u ON n.created_by = u.id
      ORDER BY n.created_at DESC";
      $result = mysqli_query($conn, $sql);
      while ($row = mysqli_fetch_assoc($result)) {
          $id_actu = $row['id'];
          echo "<div class='news-item'>";
          echo "<h3>" . htmlspecialchars($row['subject']) . "</h3>";
          echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>";
          echo "<small>Publié par " . htmlspecialchars($row['author']) . " le " . date('d/m/Y H:i', strtotime($row['created_at'])) . "</small>";  // Affichage de l'auteur et de la dates
      }
          // Formulaire commentaire
          echo "<form method='POST' action=''>";
          echo "<input type='hidden' name='id_newsletter' value='" . $id_actu . "' />";
          echo "<textarea name='commentaire' placeholder='Écris un commentaire...' required></textarea>";
          echo "<button type='submit' name='poster_commentaire'>Commenter</button>";
          echo "</form>";

          // Affichage des commentaires
          $comments = []; // Évite le warning si $_GET['id_newsletter'] n’est pas défini

            // Récupération des commentaires pour cette newsletter
            $query = "SELECT cn.*, u.username
                      FROM commentaires_newsletters cn
                      JOIN users u ON cn.id_user = u.id
                      WHERE cn.id_newsletter = $id_actu
                      ORDER BY cn.date_commentaire DESC";
            $resCom = mysqli_query($conn, $query);
            while ($com = mysqli_fetch_assoc($resCom)) {
                $comments[] = $com;
            }
            // Affichage des commentaires + réponses
          foreach ($comments as $com) {
              echo "<div class='commentaire'>";
              echo "<strong>" . htmlspecialchars($com['username']) . " :</strong> ";
              echo "<span>" . nl2br(htmlspecialchars($com['commentaire'])) . "</span>";
              echo "<small> — " . date('d/m/Y H:i', strtotime($com['date_commentaire'])) . "</small>";

              
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
                  echo "<small> — " . date('d/m/Y H:i', strtotime($rep['date_reponse'])) . "</small>";
                  echo "</div>";
              }
              
              // Formulaire de réponse
              echo "<form method='POST'>";
              echo "<input type='hidden' name='id_commentaire' value='" . $com['id'] . "' />";
              echo "<textarea name='reponse' placeholder='Répondre à ce commentaire...' required></textarea>";
              echo "<button type='submit' name='poster_reponse'>Répondre</button>";
              echo "</form>";
              echo "</div>"; // fin commentaire
          }

          // Like system
          $likeCheckQuery = "SELECT * FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur";
          $likeCheck = mysqli_query($conn, $likeCheckQuery);
          $a_liker = mysqli_num_rows($likeCheck) > 0;

          $countLikesQuery = "SELECT COUNT(*) as total FROM likes_actualites WHERE id_actualite = $id_actu";
          $countLikes = mysqli_fetch_assoc(mysqli_query($conn, $countLikesQuery))['total'];

          echo "<form method='POST' style='margin-top:10px;'>";
          echo "<input type='hidden' name='id_actualite_like' value='$id_actu'>";
          echo "<button type='submit' name='like_actualite'>";
          echo $a_liker ? "❤️ " : "🤍 ";
          echo "$countLikes Like(s)";
          echo "</button>";
          echo "</form>";

          echo "</div><hr>";
      
      ?>
    </section>

    <!-- Modal pour proposer un événement -->
    <div id="eventModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Proposer un événement</h2>
        <form method="POST">
            <label for="titre">Titre du jeu :</label>
            <input type="text" name="titre" id="titre" required>

            <label for="description">Description :</label>
            <textarea name="description" id="description" required></textarea>

            <label for="date_event">Date :</label>
            <input type="date" name="date_event" id="date_event" required>

            <button type="submit" name="submit">Ajouter</button>
        </form>
        <?php if (!empty($message)) { echo '<div class="message-popup">'.$message.'</div>'; } ?>
      </div>
    </div>
    <!-- Description Modal -->
<div id="descPopup" class="modal">
  <div class="modal-content">
    <span class="close" id="closeDesc">&times;</span>
    <h2>Description de l'événement</h2>
    <p id="eventDescriptionText"></p>
  </div>
</div>

    <footer>
      <nav>
        <span>Moussa Mehdi-Guillaume</span>
        <img src="../img/copyrighlogo.jpg" alt="Illustration copyright" />
        <ul>
          <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
          <li><a href="#mentions_legales">Mentions légales</a></li>
        </ul>
      </nav>
    </footer>
  </main>

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

    const modal = document.getElementById("eventModal");
    const btn = document.getElementById("eventButton");
    const span = document.getElementsByClassName("close")[0];

    btn.onclick = () => modal.style.display = "block";
    span.onclick = () => modal.style.display = "none";
    window.onclick = event => {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }

  function showDescription(eventId) {
    const desc = document.getElementById("desc-" + eventId).innerText;
    document.getElementById("eventDescriptionText").textContent = desc;
    document.getElementById("descPopup").style.display = "block";
  }

  document.getElementById("closeDesc").onclick = () => {
    document.getElementById("descPopup").style.display = "none";
  };

  window.onclick = function(event) {
    const modal = document.getElementById("descPopup");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };
</script>

</body>
</html>
