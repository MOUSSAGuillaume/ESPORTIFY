<?php
// Authentification basique
/*
$valid_username = 'admin';  // Utilisateur autorisé
$valid_password = getenv('PROD_PASSWORD');  // Mot de passe stocké dans l'environnement
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $valid_username ||
    $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    // Si l'utilisateur n'est pas authentifié, on lui demande de se connecter
    header('WWW-Authenticate: Basic realm="Espace sécurisé"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Accès refusé';
    exit;
}*/
?>
<?php

//connexion à la base de données
include_once(__DIR__ . '/../db.php');?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esportify - Plateforme d’événements e-sport</title>
    <meta name="description" content="Esportify est une plateforme web de gestion d’événements e-sport : création, organisation, inscription et interaction via newsletters.">
    <meta name="keywords" content="esport, événements, gaming, tournoi, PHP, newsletters, jeux vidéo, esportify">
    <meta name="author" content="Mehdi-Guillaume Moussa">
    <meta property="og:title" content="Esportify - Plateforme e-sport">
    <meta property="og:description" content="Organisez et participez à des événements e-sport facilement avec Esportify.">
    <meta property="og:image" content="https://esportify.alwaysdata.net/img/logo.png">
    <meta property="og:url" content="https://esportify.alwaysdata.net/">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="../style/accueil.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/main.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet"/>
    <link rel="icon" type="image/png" href="/img/logo.png" />

  </head>

  <body>
    <header>
      <nav class="navbar navbar-expand-md">
        <div class="container-fluid">
          <div class="row w-100 align-items-center">
            <!-- Colonne de gauche : Logo et liens de navigation -->
            <div class="col-md-12 d-flex flex-column align-items-center">
              <!-- Logo avec demi-cercle -->
              <div
                class="logo-wrapper position-relative d-flex flex-column align-items-center"
              >
                <a href="https://esportify.alwaysdata.net/frontend/accueil.php" class="navbar-brand">
                  <div
                    class="logo-container d-flex justify-content-center align-items-center">
                    <img src="/img/logo.png" alt="Esportify Logo" class="logo"/>
                  </div>
                </a>
              </div>
              <!-- Bouton hamburger -->
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
              </button>
              <!-- Liens de navigation -->
              <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav justify-content-center flex-row">
                  <li class="nav-item mt-3 mx-2">
                    <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/accueil.php"
                      >Accueil</a
                    >
                  </li>
                  <li class="nav-item mt-3 mx-2">
                    <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/connexion.php"
                      >Connexion</a
                    >
                  </li>
                  <li class="nav-item mt-3 mx-2">
                    <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/contact.php"
                      >Contact</a
                    >
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </header>
    <!-- Présentation -->
    <section class="presentation">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="row">
              <div class="col-lg-7">
                <h1 class="text-center mt-5 text-primary">Esportify</h1>
                <div class="bloc-presentation d-flex mt-5 bg-secondary">
                  <p class="textPresentation">
                    La spécialisation innovante de cette startup en pleine essor
                    dont le nom n’est plus, ni moins qu'ESPORTIFY comme vous le
                    savez déjà. Nous faisons partie de cette belle famille du
                    E-SPORT!<br /><br />
                    Et c’est avec plaisir que nous organisons divers évènements
                    proposés par nos joueurs autour de JEUX VIDÉOS, en équipe ou
                    en individuel, sur n’importe quelles consoles (PC,
                    PLAYSTATION, XBOX, SWITCH, ...).<br /><br />
                    Peu importe votre genre (Homme, Femme ou Autres), cela a
                    très peu d’importance du moment que la passion est présente.
                    Vous êtes les bienvenus pour participer et nous proposer les
                    évènements que vous souhaitez.<br /><br />
                    &nbsp;&nbsp;La compétion sens comme un orage dans l'air!!.
                  </p>
                </div>
              </div>
              <div class="col-lg-5">
                <div class="calendar-container">
                  <div class="calendar">
                    <!-- Contenu du calendrier ici -->
                    <h3 class="text-white mb-0">
                      📆 Calendrier des événements
                    </h3>
                    <?php
                      $query = "SELECT * FROM events WHERE status != 'refusé'";
                      $result = mysqli_query($conn, $query);

                      if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                          $title = htmlspecialchars($row['title']);
                          $date = htmlspecialchars($row['event_date']);
                          $description = htmlspecialchars($row['description']);
                          $status = htmlspecialchars($row['status']);  // On utilise directement la colonne de la BDD

                      // Optionnel : Couleur selon statut
                      $statusColor = match(strtolower($status)) {
                        'à venir' => '#2196F3',
                        'en cours' => '#4CAF50',
                        'terminé' => '#9E9E9E',
                        default => '#CCCCCC',
                      };

                        echo "<div class='event'>";
                        echo "<h2>$title</h2>";
                        echo "<p><strong>Date :</strong> $date</p>";
                        echo "<p><strong>Statut :</strong> <span style='color: black; background-color: $statusColor; padding: 4px 8px; border-radius: 4px;'>$status</span></p>";
                        echo "<button class='voir-description' data-description=\"$description\">Voir</button>";
                        echo "</div>";
                      }
                      } else {
                        echo "<p>Aucun événement trouvé.</p>";
                      }
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Carrousel -->
    <section>
      <div
        id="carouselExample"
        class="carousel slide position-relative w-50 mx-auto overflow-hidden shadow mt-3 mb-3 custom-max-width custom-rounded"
        data-bs-ride="carousel"
      >
        <div class="carousel-inner mt-3">
          <div class="carousel-item active">
            <img
              src="../img/img diapo/console.jpg"
              class="slides_img d-block w-100"
              alt="consoles de jeux"
            />
          </div>
          <div class="carousel-item">
            <img
              src="../img/img diapo/pc.jpg"
              class="slides_img w-100"
              alt="photo illustration ordinateur"
            />
          </div>
          <div class="carousel-item">
            <img
              src="../img/img diapo/joueur.jpg"
              class="slides_img w-100"
              alt="Gamer 1"
            />
          </div>
          <div class="carousel-item">
            <img
              src="../img/img diapo/joueur2.jpg"
              class="slides_img w-100"
              alt="Gamer 2"
            />
          </div>
        </div>
        <button
          class="carousel-control-prev"
          type="button"
          data-bs-target="#carouselExample"
          data-bs-slide="prev"
        >
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Précédent</span>
        </button>
        <button
          class="carousel-control-next"
          type="button"
          data-bs-target="#carouselExample"
          data-bs-slide="next"
        >
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Suivant</span>
        </button>
      </div>
    </section>
    <!-- Newsletters -->
    <section
      class="newsletters d-flex justify-content-center align-items-center"
    >
      <div class="container mx-auto">
        <div class="row">
          <!-- Dernières Newsletters -->
          <div class="lastnews col-md-6 mb-4">
            <div class="p-3 border rounded bg-secondary">
              <h3>📰 Dernières Newsletters</h3>
              <?php
                $resultNews = mysqli_query($conn, "SELECT subject, message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 2");
            
                if ($resultNews && mysqli_num_rows($resultNews) > 0) {
                  while ($news = mysqli_fetch_assoc($resultNews)) {
                    echo "<div class='newsletter-content'>";
                    echo "<h4>" . htmlspecialchars($news['subject']) . "</h4>";
                    echo "<p>" . nl2br(htmlspecialchars($news['message'])) . "</p>";
                    echo "<small>📅 Publié le " . date("d/m/Y", strtotime($news['created_at'])) . "</small>";
                    echo "</div><hr>";
                  }
                } else {
                  echo "<p>Aucune newsletter publiée pour le moment.</p>";
                }
              ?>
            </div>
          </div>
          <!-- Autres Newsletters -->
          <div class="anothernews col-md-6 mb-4">
            <div class="p-3 border rounded bg-secondary">
              <h3>📰 Newsletters</h3>
              <?php
                // Exemple pour une autre newsletter, ici je duplique la requête pour afficher une autre newsletter
                $resultNews2 = mysqli_query($conn, "SELECT subject, message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 2, 2");

                if ($resultNews2 && mysqli_num_rows($resultNews2) > 0) {
                while ($news2 = mysqli_fetch_assoc($resultNews2)) {
                  echo "<div class='newsletter-content'>";
                  echo "<h4>" . htmlspecialchars($news2['subject']) . "</h4>";
                  echo "<p>" . nl2br(htmlspecialchars($news2['message'])) . "</p>";
                  echo "<small>📅 Publié le " . date("d/m/Y", strtotime($news2['created_at'])) . "</small>";
                  echo "</div><hr>";
                }
                } else {
                  echo "<p>Aucune autre newsletter disponible pour le moment.</p>";
                }
              ?>
            </div>
          </div>
        </div>
        <div class="text-center">
          <button
            class="btn btn-outline-secondary me-2 mb-5"
            onclick="changeNewsletter(-1)"
          >
            &#10094;
          </button>
          <button
            class="btn btn-outline-secondary mb-5"
            onclick="changeNewsletter(1)"
          >
            &#10095;
          </button>
        </div>
      </div>
    </section>
    <!-- Footer -->
    <footer class="footer w-100 bg-secondary bg-opacity-75 py-3">
      <div class="container">
        <div class="row align-items-center text-center text-md-start">
          <div class="col-12 col-md-6 mb-2 mb-md-0">
            <span class="d-flex justify-content-center justify-content-md-start align-items-center">
              Moussa Mehdi-Guillaume
              <img
                src="/img/copyrighlogo.jpg"
                alt="Illustration copyright"
                class="ms-2"
                style="height: 20px"
              />
            </span>
          </div>
          <div class="col-12 col-md-6">
            <ul class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end list-unstyled m-0">
              <li class="mb-2 mb-md-0 me-md-2">
                <a class="footer-link" href="#politique_confidentialite">Politique de confidentialité</a>
              </li>
              <li>
                <a class="footer-link" href="#mentions_legales">Mentions légales</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>

    <?php
        mysqli_close($conn);  // Ferme la connexion à la base de données ici, une seule fois à la fin
    ?>
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

    function autoScroll() {
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



    // popup pour la description
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.voir-description').forEach(btn => {
        btn.addEventListener('click', () => {
            const description = btn.getAttribute('data-description');
            document.getElementById('popupContent').textContent = description;
            document.getElementById('popupDescription').style.display = 'flex';
        });
    });
});
</script>
<!-- Bootstrap JS via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>