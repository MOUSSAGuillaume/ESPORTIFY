<?php
session_start();
require __DIR__ . '/Router/router.php';
$routes = require __DIR__ . '/Router/allRoutes.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esportify - Plateforme d’événements e-sport</title>
    <meta name="keywords" content="esport, événements, gaming, tournoi, PHP, newsletters, jeux vidéo, esportify">
    <meta name="author" content="Mehdi-Guillaume Moussa">
    <meta property="og:title" content="Esportify - Plateforme e-sport">
    <meta property="og:description" content="Organisez et participez à des événements e-sport facilement avec Esportify.">
    <meta property="og:image" content="https://esportify.alwaysdata.net/img/logo.png">
    <meta property="og:url" content="https://esportify.alwaysdata.net/">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="../style/accueil.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style/main.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="/img/logo.png" />

</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: #1a0738;">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#" style="font-size: 1.7rem; letter-spacing: 2px;">ESPORTIFY</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item mx-2"><a class="nav-link" href="/index.php?page=accueil">Accueil</a></li>
                        <li class="nav-item mx-2"><a class="nav-link" href="#">Événements</a></li>
                        <li class="nav-item mx-2"><a class="nav-link" href="/index.php?page=contact">Contact</a></li>
                        <li class="nav-item mx-2"><a class="btn btn-primary rounded-pill px-4" href="/index.php?page=connexion">Connexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-page">
        <!-- Le contenu de la page sera injectée ici-->
        <?php
        // Charge dynamiquement le contenu de la page demandée
        $pageFile = routeRequest(); // On utilise la fonction du router
        require $pageFile;
        ?>
    </main>

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
                            style="height: 20px" />
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>