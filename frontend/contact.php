<?php
session_start(); // pour le token CSRF

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once("../db.php");
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Esportify - Plateforme d’événements e-sport</title>
        <meta name="description" content="Page de contact à Esportify - envoyer une requête via le formulaire.">
        <meta name="keywords" content="esport, événements, gaming, tournoi, PHP, newsletters, jeux vidéo, esportify">
        <meta name="author" content="Mehdi-Guillaume Moussa">
        <meta property="og:title" content="Esportify - Plateforme e-sport">
        <meta property="og:description" content="Organisez et participez à des événements e-sport facilement avec Esportify.">
        <meta property="og:image" content="https://esportify.alwaysdata.net/img/logo.png">
        <meta property="og:url" content="https://esportify.alwaysdata.net/">
        <meta property="og:type" content="website">
        <!--bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- CSS générique -->
        <link rel="stylesheet" href="/style/main.css" />
        <!-- CSS spécifique à la page de connexion -->
        <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/contact.css">
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet"/>
        <link rel="icon" type="image/png" href="/img/logo.png" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Pour les alertes -->
    </head>
    <body>
        <header>
            <nav class="navbar navbar-expand-md">
                <div class="container-fluid">
                    <div class="row w-100 align-items-center">

                        <!-- Colonne de gauche : Logo et liens de navigation -->
                        <div class="col-md-12 d-flex flex-column align-items-center">

                            <!-- Logo -->
                            <div class="logo-wrapper position-relative d-flex flex-column align-items-center">
                                <a href="https://esportify.alwaysdata.net/frontend/accueil.php" class="navbar-brand">
                                    <div class="logo-container d-flex justify-content-center align-items-center">
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
                                        <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/accueil.php">Accueil</a>
                                    </li>
                                    <li class="nav-item mt-3 mx-2">
                                        <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/connexion.php">Connexion</a>
                                    </li>
                                    <li class="nav-item mt-3 mx-2">
                                      <a class="nav-link text-white fw-bold" href="https://esportify.alwaysdata.net/frontend/contact.php">Contact</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <section class="container my-5">
            <h1 class="text-center">Contact Us</h1>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form method="POST" action="https://esportify.alwaysdata.net/backend/send_contact.php" id="contact-form" class="boxrdv">
                        <div class>
                            <label for="email" class="form-label">Email :</label>
                            <input type="email" id="email" name="email" class="form-control" required/>
                        </div>
                        <div>
                            <label for="message" class="form-label">Votre message :</label>
                            <textarea id="message" name="message" class="form-control fs-6" rows="5" required></textarea>
                        </div>
                        <!-- reCAPTCHA -->
                        <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['RECAPTCHA_SITE_KEY']; ?>"></div>

                        <!-- Ajouter le token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="d-grid">
                            <button type="submit" class="bouton-envoyer">Envoyer</button>
                         </div>
                    </form>
                </div>
             </div>
        </section>

        <!-- Footer -->
        <footer class="footer w-100 bg-secondary bg-opacity-75 py-3">
            <div class="container">
                <div class="row align-items-center text-center text-md-start">
                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <span class="d-flex justify-content-center justify-content-md-start align-items-center">Moussa Mehdi-Guillaume
                            <img src="../img/copyrighlogo.jpg" alt="Illustration copyright" class="ms-2" style="height: 20px"/>
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
          
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            document.getElementById('contact-form').addEventListener('submit', async function(e) {
                e.preventDefault(); // Empêche le rechargement

                const form = e.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });

                    const resultText = await response.text(); // <- On récupère le texte brut envoyé par send_contact.php

                    Swal.fire({
                        icon: resultText.includes("✅") ? "success" : "error",
                        title: resultText.includes("✅") ? "Succès" : "Erreur",
                        text: resultText
                    });

                    if (resultText.includes("✅")) {
                        form.reset(); // Réinitialise le formulaire si succès
                        grecaptcha.reset(); // Réinitialise le reCAPTCHA
                    }

                } catch (error) {
                    console.error("Erreur réseau : ", error);
                    alert("❌ Une erreur est survenue lors de l'envoi du message.");
                }
            });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><!-- Bootstrap JS via CDN -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script><!-- reCAPTCHA JS -->

    </body>
</html>