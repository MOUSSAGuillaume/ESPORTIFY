<?php include_once("../db.php");?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESPORTI</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/accueil.css">
</head>
<body>

<header>
    <nav class="custom-navbar">
        <!-- Logo -->
        <div class="logo-wrapper">
            <a href="/ESPORTIFY/frontend/accueil.php">
                <div class="logo-container">
                    <img src="../img/logo.png" alt="Esportify Logo" class="logo">
                </div>
            </a>
            <div class="semi-circle-outline"></div>
        </div>

        <!-- Navbar Links -->
            <div class="nav-links">
                <div class="link-container left">
                    <a href="/ESPORTIFY/frontend/accueil.php" class="link">Accueil</a>
                    <div class="connector"></div>
                </div>
                <div class="link-container center">
                    <a href="/ESPORTIFY/frontend/connexion.php" class="link">Connexion</a>
                    <div class="connector vertical"></div>
                </div>
                <div class="link-container right">
                    <a href="/ESPORTIFY/frontend/contact.php" class="link">Contact</a>
                    <div class="connector"></div>
                </div>
            </div>
    </nav>

    <!-- Section Calendrier -->
    <div class="calendar">
        <h3>📆 Calendrier des événements</h3>

        <?php
        $query = "SELECT * FROM events";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='event'>";
                echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
                echo "<p><strong>Description :</strong> " . htmlspecialchars($row['description']) . "</p>";
                echo "<p><strong>Date :</strong> " . htmlspecialchars($row['event_date']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun événement trouvé.</p>";
        }
        ?>

    </header>

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
        <p>&nbsp;&nbsp;La spécialisation innovante de cette startup en pleine essor dont le nom n’est plus , ni moins ESPORTIFY comme vous le savez deja.
            Nous faisont parti de cette belle fammile du E-SPORT! !! <br>
           &nbsp;&nbsp;Et c’est avec plaisir , que nous organisons divers évènements  proposez par nos joueurs autours de JEUX VIDEOS qui peuvent en équipe ou bien individuel
            mais aussi sur n’importe quelles consoles ( PC , PLAYSTATION , XBOX , SWITCH , ... ) <br>
           &nbsp;&nbsp;Peut importe votre genre (Homme, Femme ou bien Autres) cela à très peu d’importance du moment que la passion est présente. Vous êtes les bienvenues pour participer
            et nous proposer les évènements que vous souhaitez.</p>
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
        
        <div class="newsletter-item" id="newsletter2">
            <h3>📰 Autre Newsletter</h3>

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

    <button class="prev-newsletter" onclick="changeNewsletter(-1)">&#10094;</button>
    <button class="next-newsletter" onclick="changeNewsletter(1)">&#10095;</button>
</section>


    <div class="separator">
        <span class="diamond"></span>
        <div class="line"></div>
        <span class="diamond"></span>
    </div>

    <section class="newsletter2">
        <div class="boxNewsletter2">
            <h3>🎮 Événements à venir</h3>

            <?php

            $today = date("Y-m-d");
            $queryEvents = "SELECT title, description, event_date FROM events WHERE event_date >= '$today' ORDER BY event_date ASC LIMIT 3";
            echo $queryEvents;
            $resultEvents = mysqli_query($conn, $queryEvents);

            if ($resultEvents && mysqli_num_rows($resultEvents) > 0) {
                while ($event = mysqli_fetch_assoc($resultEvents)) {
                    echo "<div class='event-item'>";
                    echo "<h4>" . htmlspecialchars($event['title']) . "</h4>";
                    echo "<p>" . htmlspecialchars($event['description']) . "</p>";
                    echo "<small>📅 Le " . date("d/m/Y", strtotime($event['event_date'])) . "</small>";
                    echo "</div><hr>";
                }
            } else {
                echo "<p>Pas d'événements à venir pour le moment.</p>";
            }

            ?>
        </div>
    </section>
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
</script>

</body>
</html>
