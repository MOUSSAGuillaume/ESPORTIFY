<?php
include_once(__DIR__ . '/../db.php');
require_once __DIR__ . '/../vendor/autoload.php'; // Si ton composer.json est à la racine
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

//if (isset($_SESSION['user_id'])) {
//    header("Location: https://esportify.alwaysdata.net/frontend/accueil.php"); // Redirige vers la page d'accueil si l'utilisateur est déjà connecté
//   exit();
//}

if (isset($_GET['error']) && $_GET['error'] == 'captcha') {
  echo "<p class='error-message'>Veuillez valider le reCAPTCHA.</p>";
}

$pageTitle = "Connexion | Esportify";
$pageDescription = "Connecte-toi à Esportify pour participer à des événements e-sport.";
?>

<link rel="stylesheet" href="../style/connexion.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<main>
  <section class="container connexion-wrapper">
    <div class="row form-container">
      <!-- Bloc image + bouton -->
      <div class="col-12 col-md-6 image-container p-0 position-relative">
        <img src="../img/image_ecf/img2.jpg" alt="illustration" class="custom-image">
        <button onclick="location.href='https://esportify.alwaysdata.net/frontend/create_account.php'" class="btn-create">Create-account</button>
      </div>

      <!-- Bloc login -->
      <div
        class="col-12 col-md-6 login-box d-flex flex-column justify-content-center">
        <h2 class="login-title text-center">Sign in</h2>
        <?php
        if (isset($_GET['error'])) {
          if ($_GET['error'] == 1) {
            echo "<p class='error-message'>Email ou mot de passe incorrect.</p>";
          } elseif ($_GET['error'] == 2) {
            echo "<p class='error-message'>Votre compte n'est pas encore activé. Vérifiez vos emails.</p>";
          }
        }
        ?>
        <form action="/backend/login.php" method="POST" class="form-content px-4">
          <div class="mb-3 field-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control custom-input" required autocomplete="email" />
          </div>

          <div class="mb-3 field-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control custom-input" required autocomplete="current-password" />
          </div>
          <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']) ?>"></div> <!-- La clé publique de reCAPTCHA -->
          <div class="actions d-flex justify-content-between align-items-center">
            <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
              Mot de passe oublié ?
            </a>
            <button type="submit" class="btn-connecter">Connexion</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- POPUP MODALE -->
  <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content p-4">
        <div class="modal-header">
          <h2 class="modal-title" id="resetPasswordModalLabel"> Réinitialisation du mot de passe</h2>
          <p>Entrez votre email pour recevoir un lien de réinitialisation.</p>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <input type="email" id="resetEmail" class="form-control mb-3" placeholder="Email" required />
          <p id="resetMessage" class="message"></p>
          <button class="btn btn-primary w-100 reset-btn">Envoyer</button>
        </div>
      </div>
    </div>
  </div>
  <?php
  if (isset($_GET['logout'])) {
    echo '<p class="success-message">Vous avez été déconnecté avec succès.</p>';
  }
  ?>
</main>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Sélection des éléments
    const modal = document.getElementById("resetPasswordModal");
    const forgotPasswordLink = document.querySelector(".forgot-password");
    const closeModal = document.querySelector(".btn-close");
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
    forgotPasswordLink.addEventListener("click", function(event) {
      event.preventDefault();
      modal.style.display = "flex";
      resetMessage.style.display = "none"; // Cacher le message à chaque ouverture
      resetEmail.value = ""; // Réinitialiser le champ email
    });

    // Quand on clique sur la croix, on ferme la popup
    closeModal.addEventListener("click", function() {
      modal.style.display = "none";
    });

    // Fermer la popup en cliquant en dehors
    window.addEventListener("click", function(event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });

    // Gestion du clic sur "Envoyer"
    resetBtn.addEventListener("click", function() {
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
<!-- Bootstrap JS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>