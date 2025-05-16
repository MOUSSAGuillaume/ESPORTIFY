<?php
require_once __DIR__ . '/../db.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sportify - Inscription</title>
  <link rel="stylesheet" href="https://esportify.alwaysdata.net/style.css/create_account.css" />
</head>
<body>

  <div class="console-overlay" id="console-overlay">
    <div class="console-text" id="console-text"></div>
  </div>

  <header>
    <nav class="custom-navbar">
      <div class="logo-wrapper">
        <a href="https://esportify.alwaysdata.net/frontend/accueil.php"> <!-- Lien vers la page d'accueil -->
          <div class="logo-container">
            <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
          </div>
        </a>
        <div class="semi-circle-outline"></div>
      </div>
    </nav>
  </header>

  <main>
    <div class="container">
      <div class="image-form">
        <img src="../img/image_ecf/img1.webp" alt="Image de fond" class="background-image" />

        <form id="signup-form" class="form-overlay" action="https://esportify.alwaysdata.net/backend/signup.php" method="POST">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" placeholder="email" required />

          <label for="pseudo">Pseudo:</label>
         <input type="text" id="pseudo" name="username" placeholder="Pseudo" required />

        <label for="password">Mot de passe:</label>
        <input type="password" id="password" name="mot_de_passe" placeholder="mot de passe" required />
        <p class="password-rules">
          * Au moins 8 caractères avec 1 chiffre, 1 minuscule, 1 majuscule et un caractère spécial.
        </p>

         <label for="confirm-password">Confirmer mot de passe:</label>
        <input type="password" id="confirm-password" name="confirmer_mot_de_passe" placeholder="Confirmer mot de passe" required />

        <p id="form-error" style="color: red; text-align: center;"></p>
          <button type="submit">Create</button>
        </form>
      </div>
    </div>

    <!-- POPUP confirmation -->
    <div id="confirmationPopup" class="confirmation-popup">
      <div class="popup-content">
        <p>Un mail vous a été envoyé.<br>Merci de cliquer sur le lien pour valider votre compte.</p>
        <button onclick="closeConfirmation()">Fermer</button>
      </div>
    </div>
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
    // Simulation d'une console
    const consoleText = document.getElementById("console-text");
    const overlay = document.getElementById("console-overlay");
    const lines = [
      "Initialisation du système...",
      "Chargement des modules...",
      "Connexion à Sportify établie ✔",
      "Chargement de l'interface..."
    ];

    let index = 0;

    function typeLine() {
      if (index < lines.length) {
        consoleText.textContent += lines[index] + "\n";
        index++;
        setTimeout(typeLine, 600);
      } else {
        setTimeout(() => {
          overlay.remove();
          const flash = document.createElement("div");
          flash.classList.add("screen-flash");
          document.body.appendChild(flash);
          setTimeout(() => {
            flash.remove();
            document.getElementById("signup-form").classList.add("show");
          }, 600);
        }, 1000);
      }
    }

    typeLine();

    // Gestion du formulaire
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
        // ✅ Affiche la popup de confirmation
        confirmationPopup.style.display = "block";
      } else {
        // ⚠️ Affiche le message d'erreur retourné
        errorMsg.textContent = result;
      }

    } catch (err) {
      errorMsg.textContent = "❌ Une erreur est survenue. Veuillez réessayer.";
      console.error(err);
    }
  });
});



    function closeConfirmation() {
      document.getElementById("confirmationPopup").style.display = "none";
      window.location.href = "/frontend/accueil.php"; // Rediriger vers la page d'accueil après la confirmation
    }
  </script>

</body>
</html>
