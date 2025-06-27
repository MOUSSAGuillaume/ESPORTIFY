<?php
require_once __DIR__ . '/../db.php';
$pageTitle = "Inscription | Esportify";
$pageDescription = "Esportify est une plateforme web de gestion d’événements e-sport : création, organisation, inscription et interaction via newsletters";
?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/create_account.css">

<!-- Effet console au chargement -->
<div class="console-overlay" id="console-overlay">
  <div class="console-text" id="console-text"></div>
</div>

<div class="container-signup" id="signup-block" style="display:none;">
  <div class="console-image-bg" style="position:relative; display:flex; justify-content:center;">
    <img src="../img/image_ecf/img1.webp" alt="Console de jeu" class="console-real-img" style="max-width:100%; height:auto;" />

    <!-- Formulaire positionné au centre de l’écran de la console -->
    <form id="signup-form" class="signup-form-overlay" action="https://esportify.alwaysdata.net/backend/signup.php" method="POST" autocomplete="off">
      <h2 class="mb-4 text-center text-primary">Créer un compte</h2>
      <div class="mb-3">
        <label for="email" class="form-label">Adresse Email</label>
        <input type="email" class="form-control" id="email" name="email" required autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="pseudo" class="form-label">Pseudo</label>
        <input type="text" class="form-control" id="pseudo" name="username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="mot_de_passe" required>
        <div class="form-text password-rules">
          * Au moins 8 caractères, 1 chiffre, 1 minuscule, 1 majuscule, 1 caractère spécial.
        </div>
      </div>
      <div class="mb-3">
        <label for="confirm-password" class="form-label">Confirmer le mot de passe</label>
        <input type="password" class="form-control" id="confirm-password" name="confirmer_mot_de_passe" required>
      </div>
      <div id="form-error" class="text-danger fw-bold mb-3 text-center"></div>
      <button type="submit" class="btn btn-primary w-100">Créer mon compte</button>
    </form>
  </div>
</div>

<!-- POPUP confirmation -->
<div id="confirmationPopup" class="confirmation-popup" style="display:none;">
  <div class="popup-content">
    <p>Un mail vous a été envoyé.<br>Merci de cliquer sur le lien pour valider votre compte.</p>
    <button class="btn btn-outline-primary" onclick="closeConfirmation()">Fermer</button>
  </div>
</div>

<script>
  // --- Animation console ---
  const consoleText = document.getElementById("console-text");
  const overlay = document.getElementById("console-overlay");
  const signupBlock = document.getElementById("signup-block");
  const lines = [
    "Initialisation du système...",
    "Chargement des modules...",
    "Connexion à Esportify établie ✔",
    "Chargement de l'interface..."
  ];
  let index = 0;

  function typeLine() {
    if (index < lines.length) {
      consoleText.textContent += lines[index] + "\n";
      index++;
      setTimeout(typeLine, 650);
    } else {
      setTimeout(() => {
        overlay.style.display = "none";
        signupBlock.style.display = "block";
        signupBlock.style.opacity = 1;
      }, 1000);
    }
  }
  typeLine();

  // --- Gestion du formulaire (valide et affiche confirmation) ---
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("signup-form");
    const confirmationPopup = document.getElementById("confirmationPopup");
    const errorMsg = document.getElementById("form-error");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      errorMsg.textContent = "";

      const email = document.getElementById("email").value.trim();
      const pseudo = document.getElementById("pseudo").value.trim();
      const password = document.getElementById("password").value.trim();
      const confirmPassword = document.getElementById("confirm-password").value.trim();

      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

      if (password !== confirmPassword) {
        errorMsg.textContent = "❌ Les mots de passe ne sont pas identiques.";
        return;
      }

      if (!passwordRegex.test(password)) {
        errorMsg.textContent = "❌ Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        return;
      }

      try {
        const response = await fetch("https://esportify.alwaysdata.net/backend/signup.php", {
          method: "POST",
          body: new URLSearchParams({
            email,
            username: pseudo,
            mot_de_passe: password,
            confirmer_mot_de_passe: confirmPassword
          }),
        });
        const result = await response.text();

        if (result.includes("success")) {
          confirmationPopup.style.display = "flex";
        } else {
          errorMsg.textContent = result;
        }
      } catch (err) {
        errorMsg.textContent = "❌ Une erreur est survenue. Veuillez réessayer.";
        console.error(err);
      }
    });
  });

  // --- Popup confirmation ---
  function closeConfirmation() {
    document.getElementById("confirmationPopup").style.display = "none";
    window.location.href = "/index.php?page=accueil";
  }
</script>

<div id="rotate-message" class="rotate-message">
  <div>
    <svg width="70" height="70" fill="none" viewBox="0 0 70 70">
      <rect x="10" y="22" width="50" height="26" rx="7" fill="#3b2d58"/>
      <rect x="18" y="32" width="34" height="6" rx="3" fill="#7357ee"/>
      <path d="M 35 10 L 35 18" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
      <path d="M 35 52 L 35 60" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <br>
    <strong>Tournez votre téléphone<br>en mode paysage !</strong>
  </div>
</div>