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
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/accueil.css">
</head>
<body>

<header>
    <nav class="custom-navbar">
        <!-- Logo -->
        <div class="logo-wrapper">
            <a href="https://esportify.alwaysdata.net/frontend/accueil.php">
                <div class="logo-container">
                    <img src="/img/logo.png" alt="Esportify Logo" class="logo">
                </div>
            </a>
            <div class="semi-circle-outline"></div>
        </div>

        <!-- Navbar Links -->
            <div class="nav-links">
                <div class="link-container left">
                    <a href="https://esportify.alwaysdata.net/frontend/accueil.php" class="link">Accueil</a>
                    <div class="connector"></div>
                </div>
                <div class="link-container center">
                    <a href="https://esportify.alwaysdata.net/frontend/connexion.php" class="link">Connexion</a>
                    <div class="connector vertical"></div>
                </div>
                <div class="link-container right">
                    <a href="https://esportify.alwaysdata.net/frontend/contact.php" class="link">Contact</a>
                    <div class="connector"></div>
                </div>
            </div>
    </nav>
</header>
    <!-- Section Calendrier -->
    <div class="calendar">
        <h3>📆 Calendrier des événements</h3>

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


<section class="ctaContainer">
    <h1 class="titrePrincipal">Esportify</h1>
</section>

<?php
if (isset($_GET['logout'])) {
    echo '<p class="success-message">Vous avez été déconnecté avec succès.</p>';
}
?>

<!-- Section Présentation -->
<section class="presentation">
    <div class="textPresentation">
        <p>
            &nbsp;&nbsp;Esportify est une startup en plein essor, spécialisée dans l’organisation d’événements e-sport.
            Comme vous le savez déjà, nous faisons partie intégrante de cette grande famille du E-SPORT !<br><br>
            &nbsp;&nbsp;C’est avec enthousiasme que nous organisons une multitude d’événements proposés par nos joueurs,
            qu’ils soient en solo ou en équipe, et sur toutes les plateformes : PC, PlayStation, Xbox, Nintendo Switch, etc.<br><br>
            &nbsp;&nbsp;Quel que soit votre genre (Homme, Femme ou Autre), cela importe peu tant que la passion est présente.
            Vous êtes les bienvenus pour participer, interagir, et surtout proposer les événements que vous aimeriez voir naître. <br><br>
            &nbsp;&nbsp;La compétion sens comme un orage dans l'air!!.
        </p>
    </div>
</section>

<!-- Section Diaporama -->
<section>
    <div class="slideshow-container">
        <button class="btn prev" onclick="changeSlide(-1)">&#10094;</button>
        <div class="slides">
            <img src="../img/img diapo/console.jpg" alt="Console">
            <img src="../img/img diapo/joueur.jpg" alt="Joueur">
            <img src="../img/img diapo/joueur2.jpg" alt="Joueur 2">
            <img src="../img/img diapo/pc.jpg" alt="PC">
        </div>
        <button class="btn next" onclick="changeSlide(1)">&#10095;</button>
    </div>
</section>

<!-- Section Newsletters -->
<section class="newsletters-container">
    <div class="newsletter-wrapper">
        <div class="newsletter-item" id="newsletter1">
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


        <div class="newsletter-wrapper">
            <div class="newsletter-item" id="newsletter2">
                <h3>📰 Newsletter</h3>

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
</section>
    

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

<?php
mysqli_close($conn);  // Ferme la connexion à la base de données ici, une seule fois à la fin
?>
    <div id="popupDescription" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000;">
        <div style="background:white; padding:20px; border-radius:8px; max-width:600px; position:relative;">
            <h3>Description de l'événement</h3>
            <p id="popupContent"></p>
            <button onclick="document.getElementById('popupDescription').style.display='none'"
            style="position:absolute; top:10px; right:10px;">Fermer</button>
        </div>
    </div>

<!-- Script Diaporama -->
<script>
    let index = 0;
    const slides = document.querySelector(".slides");
    const totalSlides = document.querySelectorAll(".slides img").length;

    function changeSlide(direction) {
        index = (index + direction + totalSlides) % totalSlides;
        slides.style.transform = `translateX(-${index * 100}%)`;
    }

    // Défilement automatique toutes les 4 secondes
    setInterval(() => {
        changeSlide(1);
    }, 4000);

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

</body>
</html>