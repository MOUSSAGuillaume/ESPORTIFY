<?php
include_once("../db.php");
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: ../frontend/connexion.php");
    exit;
}

// Vérifie que l'utilisateur a le bon rôle
if ($_SESSION['user']['role'] !== 4) { // 4 pour Joueur
    header("Location: ../frontend/accueil.php");
    exit;
}


$username = $_SESSION['user']['pseudo'];
$id_joueur = $_SESSION['user']['id'];

// Traitement du formulaire événement
$message = "";
if (isset($_POST['submit'])) {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_event = $_POST['date_event'];
    $nb_joueurs = (int) $_POST['nb_joueurs'];


    $sql = "INSERT INTO events (title, description, event_date, status, nb_max_participants)
        VALUES ('$titre', '$description', '$date_event', 'en attente', $nb_joueurs)";




    if (mysqli_query($conn, $sql)) {
        $message = "<p style='color:green;'>✅ Événement ajouté avec succès !</p>";
    } else {
        $message = "<p style='color:red;'>❌ Erreur : " . mysqli_error($conn) . "</p>";
    }
}

// Traitement de l'inscription à un événement
if (isset($_POST['inscription_event'])) {
  $event_id = (int) $_POST['event_id'];
  $user_id = $_SESSION['user']['id'];

// Traitement de la désinscription
if (isset($_POST['desinscription_event'])) {
  $event_id = (int) $_POST['event_id'];
  $user_id = $_SESSION['user']['id'];

  // Vérifie si le nombre max de joueurs est atteint
$eventInfo = mysqli_query($conn, "SELECT nb_joueurs FROM events WHERE id = $event_id");
if ($eventInfo && mysqli_num_rows($eventInfo) > 0) {
    $max = (int) mysqli_fetch_assoc($eventInfo)['nb_joueurs'];

    $resCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM inscriptions WHERE event_id = $event_id");
    $nbInscrits = (int) mysqli_fetch_assoc($resCount)['total'];

    if ($nbInscrits >= $max) {
        $message = "<p style='color:red;'>❌ Nombre maximum de participants atteint.</p>";
        return;
    }
}

  $stmt = $conn->prepare("DELETE FROM inscriptions WHERE user_id = ? AND event_id = ?");
  if ($stmt) {
      $stmt->bind_param("ii", $user_id, $event_id);
      if ($stmt->execute()) {
          $message = "<p style='color:orange;'>🚫 Désinscription réussie.</p>";
      } else {
          $message = "<p style='color:red;'>❌ Erreur lors de la désinscription : " . $stmt->error . "</p>";
      }
      $stmt->close();
  } else {
      $message = "<p style='color:red;'>❌ Erreur de préparation de la requête pour la désinscription.</p>";
  }
}


  // Vérifie si déjà inscrit
  $checkInscription = mysqli_query($conn, "SELECT * FROM inscriptions WHERE user_id = $user_id AND event_id = $event_id");
  if (mysqli_num_rows($checkInscription) == 0) {
      $date_inscription = date('Y-m-d H:i:s');
      $statut = 'en_attente';

      $stmt = $conn->prepare("INSERT INTO inscriptions (user_id, event_id, date_inscription, statut) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iiss", $user_id, $event_id, $date_inscription, $statut);
    if ($stmt->execute()) {
        $message = "<p style='color:green;'>✅ Inscription envoyée !</p>";
    } else {
        $message = "<p style='color:red;'>❌ Erreur lors de l’inscription : " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    $message = "<p style='color:red;'>❌ Erreur de préparation de la requête.</p>";


      }
  } else {
      $message = "<p style='color:orange;'>⚠️ Vous êtes déjà inscrit à cet événement.</p>";
  }
}

// Traitement du commentaire
if (isset($_POST['poster_commentaire'])) {
    $id_actu = (int) $_POST['id_actualite'];
    $commentaire = mysqli_real_escape_string($conn, $_POST['commentaire']);

    $sqlComment = "INSERT INTO commentaires_actualites (id_actualite, id_joueur, commentaire)
                   VALUES ('$id_actu', '$id_joueur', '$commentaire')";
    mysqli_query($conn, $sqlComment);
    header("Location: joueur_dashboard.php");
    exit;
}

// Récupération des événements
$evenements = [];
$resultEvents = mysqli_query($conn, "SELECT * FROM events WHERE status = 'accepté' ORDER BY event_date DESC");
if ($resultEvents) {
    while ($event = mysqli_fetch_assoc($resultEvents)) {
        $evenements[] = $event;
    }
}
// Like actualité
if (isset($_POST['like_actualite'])) {
  $id_actu = (int) $_POST['id_actualite_like'];
  $id_joueur = $_SESSION['user']['id'];

  // Vérifie si déjà liké
  $check = mysqli_query($conn, "SELECT * FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur");
  if (mysqli_num_rows($check) == 0) {
      mysqli_query($conn, "INSERT INTO likes_actualites (id_actualite, id_joueur) VALUES ($id_actu, $id_joueur)");
  } else {
      // Si déjà liké, on supprime (toggle like)
      mysqli_query($conn, "DELETE FROM likes_actualites WHERE id_actualite = $id_actu AND id_joueur = $id_joueur");
  }
}

if (isset($_POST['poster_reponse'])) {
  $id_commentaire = (int) $_POST['id_commentaire'];
  $id_joueur = $_SESSION['user']['id'];
  $reponse = mysqli_real_escape_string($conn, $_POST['reponse']);

  $sqlReponse = "INSERT INTO reponses_commentaires (id_commentaire, id_joueur, reponse)
                 VALUES ('$id_commentaire', '$id_joueur', '$reponse')";
  mysqli_query($conn, $sqlReponse);
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

  <main class="hidden" id="dashboard-content">
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
        <a href="/ESPORTIFY/frontend/mon_profil.php" class="btn">Mon profil</a>
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
                    $resCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM inscriptions WHERE event_id = $id_event");
                    $count = mysqli_fetch_assoc($resCount)['total'];
                      echo $count . " joueur(s)";
                  ?>
                </td>
                <td>
                  <?php
                  // Vérifie si l'utilisateur est déjà inscrit
                  $check = mysqli_query($conn, "SELECT * FROM inscriptions WHERE event_id = $id_event AND user_id = $id_joueur");
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
      $newsQuery = "SELECT * FROM actualites ORDER BY date_pub DESC";
      $result = mysqli_query($conn, $newsQuery);
      while ($row = mysqli_fetch_assoc($result)) {
          $id_actu = $row['id'];
          echo "<div class='news-item'>";
          echo "<h3>" . htmlspecialchars($row['titre']) . "</h3>";
          echo "<p>" . nl2br(htmlspecialchars($row['contenu'])) . "</p>";
          echo "<small>Publié le " . date('d/m/Y H:i', strtotime($row['date_pub'])) . "</small>";

          // Formulaire commentaire
          echo "<form method='POST' action=''>";
          echo "<input type='hidden' name='id_actualite' value='" . $id_actu . "' />";
          echo "<textarea name='commentaire' placeholder='Écris un commentaire...' required></textarea>";
          echo "<button type='submit' name='poster_commentaire'>Commenter</button>";
          echo "</form>";

          // Affichage des commentaires
          $commentQuery = "SELECT ca.*, u.pseudo FROM commentaires_actualites ca
                           JOIN users u ON ca.id_joueur = u.id
                           WHERE ca.id_actualite = $id_actu
                           ORDER BY date_commentaire DESC";
          $comments = mysqli_query($conn, $commentQuery);

          while ($com = mysqli_fetch_assoc($comments)) {
              echo "<div class='commentaire'>";
              echo "<strong>" . htmlspecialchars($com['pseudo']) . " :</strong> ";
              echo "<span>" . nl2br(htmlspecialchars($com['commentaire'])) . "</span>";
              echo "<small> — " . date('d/m/Y H:i', strtotime($com['date_commentaire'])) . "</small>";

              // Formulaire de réponse
              echo "<form method='POST' style='margin-top:5px;'>";
              echo "<input type='hidden' name='id_commentaire' value='" . $com['id'] . "' />";
              echo "<textarea name='reponse' placeholder='Répondre à ce commentaire...' required></textarea>";
              echo "<button type='submit' name='poster_reponse'>Répondre</button>";
              echo "</form>";

              // Réponses
              $id_commentaire = $com['id'];
              $repQuery = "SELECT rc.*, u.pseudo FROM reponses_commentaires rc
                           JOIN users u ON rc.id_joueur = u.id
                           WHERE rc.id_commentaire = $id_commentaire
                           ORDER BY date_reponse DESC";
              $reponses = mysqli_query($conn, $repQuery);

              while ($rep = mysqli_fetch_assoc($reponses)) {
                  echo "<div class='reponse' style='margin-left:20px; padding-left:10px; border-left: 1px solid #ccc;'>";
                  echo "<strong>" . htmlspecialchars($rep['pseudo']) . " (réponse) :</strong> ";
                  echo "<span>" . nl2br(htmlspecialchars($rep['reponse'])) . "</span>";
                  echo "<small> — " . date('d/m/Y H:i', strtotime($rep['date_reponse'])) . "</small>";
                  echo "</div>";
              }

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
      }
      ?>
    </section>

    <!-- Modal pour proposer un événement -->
    <div id="eventModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Proposer un événement</h2>
        <form method="POST">
            <label for="titre">Titre :</label>
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
  </script>
<!-- Description Modal -->
<div id="descPopup" class="modal">
  <div class="modal-content">
    <span class="close" id="closeDesc">&times;</span>
    <h2>Description de l'événement</h2>
    <p id="eventDescriptionText"></p>
  </div>
</div>

<script>
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
