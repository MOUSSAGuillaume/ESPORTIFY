<?php

//connexion à la base de données
include_once(__DIR__ . '/../db.php');

$pageTitle = "Accueil | Esportify";
$pageDescription = "Esportify est une plateforme web de gestion d’événements e-sport : création, organisation, inscription et interaction via newsletters";
?>



<section class="esportify-hero-bg" style="background: url('../img/center1.png') center/cover; min-height: 900px; padding-bottom:60px;">
  <div class="container">
    <!-- Hero : titre + image -->
    <div class="row align-items-center flex-lg-row flex-column-reverse py-5">
      <div class="col-lg-7 text-white text-lg-start text-center">
        <h1 class="display-4 fw-bold mb-3" style="letter-spacing:2px; color: #4fc3f7;">
          Esportify
        </h1>
        <p class="lead mb-4">
          Esportify, la plateforme dédiée à la gestion d'événements e-sport : crée, organise, participe, propose, vibre avec la communauté gaming !
        </p>
        <a href="#events" class="btn btn-lg btn-primary rounded-pill shadow px-5" style="font-weight:600;">
          Voir les événements
        </a>
      </div>
      <div class="col-lg-5 text-center mb-4 mb-lg-0">
        <img src="../img/deco_transparent.png" alt="image gaming" class="img-fluid shadow-lg rounded-4 " style="max-width: 350px;">
      </div>
    </div>

    <!-- Présentation + calendrier -->
    <div class="row g-4 align-items-start">
      <div class="col-lg-7">
        <div class="bg-secondary p-4 rounded-4 shadow-sm" style="min-height: 250px;">
          <h2 class="mb-3" style="color:rgb(239, 236, 229);">Bienvenue sur Esportify</h2>
          <p class="fs-5" style="color:rgb(239, 236, 229);">
            Startup en pleine croissance, Esportify rassemble les passionnés de e-sport : joueurs, organisateurs et fans autour de tournois multi-plateformes (PC, PlayStation, Xbox, Switch…). Peu importe ton genre ou ton niveau, l'essentiel c'est la passion !<br><br>
            <strong>Propose, participe et vis la compétition autrement !</strong>
          </p>
        </div>
      </div>
      <div class="container py-5" id="events">
        <h2 class="text-white text-center mb-5">Calendrier des événements</h2>
        <div class="event-horizontal-scroll d-flex flex-row gap-4 overflow-auto py-3">
          <?php
          $query = "SELECT * FROM events WHERE status != 'refusé' ORDER BY event_date ASC";
          $result = mysqli_query($conn, $query);
          if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $title = htmlspecialchars($row['title']);
              $date = htmlspecialchars($row['event_date']);
              $description = htmlspecialchars($row['description']);
              $status = htmlspecialchars($row['status']);
              $statusColor = match (strtolower($status)) {
                'à venir' => '#2196F3',
                'en cours' => '#4CAF50',
                'terminé' => '#9E9E9E',
                default => '#CCCCCC',
              };
              echo '<div class="event-card-pro shadow-lg p-4 rounded-4 bg-dark text-white" style="min-width:330px; max-width:330px; flex:0 0 330px;">';
              echo "    <h5 class='mb-2 fw-bold'>$title</h5>";
              echo "    <div class='mb-1'><span class='me-2'>🗓️</span><strong>Date :</strong> " . date("d/m/Y H:i", strtotime($date)) . "</div>";
              echo "    <span class='badge mb-3' style='background:$statusColor; color:#fff; padding:5px 12px;'>$status</span>";
              echo "    <p style='min-height: 60px;'>" . substr($description, 0, 80) . "...</p>";
              echo "    <button class='btn btn-sm btn-outline-primary voir-description' data-description=\"$description\">Voir plus</button>";
              echo '</div>';
            }
          } else {
            echo "<p class='text-light text-center'>Aucun événement trouvé.</p>";
          }
          ?>
        </div>
      </div>


    </div>
  </div>
</section>

<!-- Carrousel stylé -->
<section>
  <div id="carouselExample" class="carousel slide mx-auto mt-4 mb-4 shadow-lg" style="max-width: 600px; border-radius: 18px; overflow: hidden;"   data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active"><img src="../img/img diapo/console.jpg" class="d-block w-100" alt="consoles de jeux"></div>
      <div class="carousel-item"><img src="../img/img diapo/pc.jpg" class="d-block w-100" alt="photo illustration ordinateur"></div>
      <div class="carousel-item"><img src="../img/img diapo/joueur.jpg" class="d-block w-100" alt="Gamer 1"></div>
      <div class="carousel-item"><img src="../img/img diapo/joueur2.jpg" class="d-block w-100" alt="Gamer 2"></div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Précédent</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span><span class="visually-hidden">Suivant</span>
    </button>
  </div>
</section>

<!-- Newsletters modernisé -->
<section class="newsletters py-5" style="background: url('../img/center1.png') center/cover; min-height: 900px; ">
  <div class="container">
    <h2 class="text-white text-center mb-5">📰 Dernières Newsletters</h2>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="p-4 border-0 rounded-4 bg-secondary shadow">
          <?php
          $resultNews = mysqli_query($conn, "SELECT subject, message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 2");
          if ($resultNews && mysqli_num_rows($resultNews) > 0) {
            while ($news = mysqli_fetch_assoc($resultNews)) {
              echo "<div class='newsletter-content mb-4'>";
              echo "<h4 class='fw-semibold'>" . htmlspecialchars($news['subject']) . "</h4>";
              echo "<p>" . nl2br(htmlspecialchars($news['message'])) . "</p>";
              echo "<small class='text-light'>📅 Publié le " . date("d/m/Y", strtotime($news['created_at'])) . "</small>";
              echo "</div><hr class='bg-light'>";
            }
          } else {
            echo "<p class='text-light'>Aucune newsletter publiée pour le moment.</p>";
          }
          ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-4 border-0 rounded-4 bg-secondary shadow">
          <?php
          $resultNews2 = mysqli_query($conn, "SELECT subject, message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 2, 2");
          if ($resultNews2 && mysqli_num_rows($resultNews2) > 0) {
            while ($news2 = mysqli_fetch_assoc($resultNews2)) {
              echo "<div class='newsletter-content mb-4'>";
              echo "<h4 class='fw-semibold'>" . htmlspecialchars($news2['subject']) . "</h4>";
              echo "<p>" . nl2br(htmlspecialchars($news2['message'])) . "</p>";
              echo "<small class='text-light'>📅 Publié le " . date("d/m/Y", strtotime($news2['created_at'])) . "</small>";
              echo "</div><hr class='bg-light'>";
            }
          } else {
            echo "<p class='text-light'>Aucune autre newsletter disponible pour le moment.</p>";
          }
          ?>
        </div>
      </div>
    </div>
    <div class="text-center mt-4">
      <button class="btn btn-outline-light me-2" onclick="changeNewsletter(-1)">&#10094;</button>
      <button class="btn btn-outline-light" onclick="changeNewsletter(1)">&#10095;</button>
    </div>
  </div>
</section>

<!-- Popup et scripts restent les mêmes que dans ton code d'origine -->
<?php mysqli_close($conn); ?>
<div id="popupDescription" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:rgba(26,7,56);color:wheat; padding:20px; border-radius:8px; max-width:600px; position:relative;">
    <h3>Description de l'événement</h3>
    <p id="popupContent"></p>
    <button onclick="document.getElementById('popupDescription').style.display='none'"
      style="position:absolute; top:10px; right:10px;">Fermer</button>
  </div>
</div>

<script>
  // défilement automatique du calendrier
  const container = document.querySelector('.calendar');

  /* function autoScroll() {
    if ((container.scrollLeft + container.clientWidth) >= container.scrollWidth) {
      container.scrollLeft = 0; // retour au début
    } else {
      container.scrollLeft += 2; // avance douce
    }
  }

  let scrollInterval = setInterval(autoScroll, 20); // + rapide = + fluide

  // Arrêt si l'utilisateur interagit (optionnel)
  container.addEventListener('mouseenter', () => clearInterval(scrollInterval));
  container.addEventListener('mouseleave', () => scrollInterval = setInterval(autoScroll, 20));
*/


  // popup pour la description
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.voir-description').forEach(btn => {
      btn.addEventListener('click', () => {
        const description = btn.getAttribute('data-description');
        document.getElementById('popupContent').textContent = description;
        document.getElementById('popupDescription').style.display = 'flex';
      });
    });
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>