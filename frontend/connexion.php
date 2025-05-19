<?php include_once("../db.php");
require_once __DIR__ . '/../vendor/autoload.php'; // Si ton composer.json est à la racine
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: https://esportify.alwaysdata.net/frontend/accueil.php"); // Redirige vers la page d'accueil si l'utilisateur est déjà connecté
    exit();
}

if (isset($_GET['error']) && $_GET['error'] == 'captcha') {
    echo "<p class='error-message'>Veuillez valider le reCAPTCHA.</p>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de connexion à Esportify - Connectez-vous ou créez un compte.">
    <title>ESPORTI</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/connexion.css"/>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

    <header>
        <nav class="custom-navbar">
            <!-- Conteneur du logo + demi-cercle -->
            <div class="logo-wrapper">
                <a href="https://esportify.alwaysdata.net/frontend/accueil.php">  <!-- Lien vers la page d'accueil -->
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo">
                    </div>
                </a>
                <div class="semi-circle-outline"></div>
            </div>
        </nav>
    </header>

    <main>
        <section class="container">
            <!-- IMAGE + BOUTON DEDANS -->
            <div class="imageCompte">
                <img src="../img/image_ecf/img2.jpg" alt="Imagedecompte">
                <button onclick="location.href='https://esportify.alwaysdata.net/frontend/create_account.php'" class="create-account-btn">Create-account</button>
                
            </div>

            <div class="login-box">
                <h2>Sign in</h2>
                <!--<?php
                if (isset($_GET['error'])) {
                    echo "<p class='error-message'>Email ou mot de passe incorrect.</p>";
                }
                ?>-->
                <form action="../backend/login.php" method="POST">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" autocomplete="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Password" required>
                    </div>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']) ?>"></div> <!-- La clé publique de reCAPTCHA -->
                    <button type="submit" class="login-btn">Connexion</button>
                </form>
            </div>
        </section>

        <!-- POPUP MODALE -->
        <div id="resetPasswordModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Réinitialisation du mot de passe</h2>
                <p>Entrez votre email pour recevoir un lien de réinitialisation.</p>
                <input type="email" id="resetEmail" placeholder="Email" required>
                <p id="resetMessage" class="message"></p>
                <button class="reset-btn">Envoyer</button>
            </div>
        </div>
        <?php
            if (isset($_GET['logout'])) {
            echo '<p class="success-message">Vous avez été déconnecté avec succès.</p>';
            }?>

    </main>

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

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Sélection des éléments
        const modal = document.getElementById("resetPasswordModal");
        const forgotPasswordLink = document.querySelector(".forgot-password");
        const closeModal = document.querySelector(".close");
        const resetEmail = document.getElementById("resetEmail");
        const resetBtn = document.querySelector(".reset-btn");
        const resetMessage = document.getElementById("resetMessage");

        // Masquer le popup au chargement
        modal.style.display = "none";

        // Fonction pour valider l'email
        function validateEmail(email) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email);
        }

        // Quand on clique sur "Mot de passe oublié ?", on affiche la popup
        forgotPasswordLink.addEventListener("click", function (event) {
            event.preventDefault();
            modal.style.display = "flex";
            resetMessage.style.display = "none"; // Cacher le message à chaque ouverture
            resetEmail.value = ""; // Réinitialiser le champ email
        });

        // Quand on clique sur la croix, on ferme la popup
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
        });

        // Fermer la popup en cliquant en dehors
        window.addEventListener("click", function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });

        // Gestion du clic sur "Envoyer"
        resetBtn.addEventListener("click", function () {
            const email = resetEmail.value.trim();

            if (email === "") {
                resetMessage.textContent = "Veuillez entrer votre email.";
                resetMessage.className = "message error";
                resetMessage.style.display = "block";
            } else if (!validateEmail(email)) {
                resetMessage.textContent = "Veuillez entrer un email valide.";
                resetMessage.className = "message error";
                resetMessage.style.display = "block";
            } else {
        // Envoi de la requête AJAX pour envoyer l'email de réinitialisation
             const formData = new FormData();
            formData.append("email", email);

            fetch("https://esportify.alwaysdata.net/backend/reset_pass_request.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                resetMessage.textContent = data;
                resetMessage.className = "message success";
                resetMessage.style.display = "block";
            })
            .catch(error => {
                resetMessage.textContent = "Une erreur s'est produite. Veuillez réessayer.";
                resetMessage.className = "message error";
                resetMessage.style.display = "block";
            });
        }
    });
});


    </script>
<?php
if (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        echo "<p class='error-message'>Email ou mot de passe incorrect.</p>";
    } elseif ($_GET['error'] == 2) {
        echo "<p class='error-message'>Votre compte n'est pas encore activé. Vérifiez vos emails.</p>";
    }
}
?>


</body>
</html>