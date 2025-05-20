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
    <meta name="description" content="Page de contact à Esportify - envoyer une requête via le formulaire.">
    <title>ESPORTI</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/contact.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <header>
        <nav class="custom-navbar">
            <!-- Conteneur du logo + demi-cercle -->
            <div class="logo-wrapper">
                <div class="logo-container">
                    <img src="../img/logo.png" alt="Esportify Logo" class="logo">
                </div>
                <div class="semi-circle-outline"></div>
            </div>
    
            <!-- Conteneur des liens avec connecteurs -->
            <div class="nav-links">
                <div class="link-container left">
                    <a href="https://esportify.alwaysdata.net/frontend/accueil.php" class="link">Accueil</a>
                    <div class="connector">
                    </div>
                </div>
                <div class="link-container center">
                    <a href="https://esportify.alwaysdata.net/frontend/connexion.php" class="link">Connexion</a>
                    <div class="connector vertical">
                    </div>
                </div>
                <div class="link-container right">
                    <a href="https://esportify.alwaysdata.net/frontend/contact.php" class="link">Contact</a>
                    <div class="connector">
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <section>
    <h1>Contact Us</h1>
    <div class="boxrdv" id="formulaire">
        <div class="formulaire">
            <form method="POST" action="https://esportify.alwaysdata.net/backend/send_contact.php" id="contact-form">
    
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required />
    
                <label for="message">Votre message :</label>
                <textarea id="message" name="message" required></textarea>
    
                <!-- reCAPTCHA -->
                <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['RECAPTCHA_SITE_KEY']; ?>"></div>
    
                <!-- Ajouter le token CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </section>
    

        <footer>
            <nav>
              <span>Moussa Mehdi-Guillaume</span>
              <img src="../img/copyrighlogo.jpg" alt="Illustration copyright" />
              <ul>
                <li>
                  <a href="#politique_confidentialite">Politique de confidentialité</a>
                </li>
                <li><a href="#mentions_legales">Mentions légales</a></li>
              </ul>
            </nav>
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


</body>
</html>