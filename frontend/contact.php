<?php include_once("../db.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESPORTI</title>
    <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/contact.css">
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
    
                <!-- Ajouter le token reCAPTCHA -->
                <input type="hidden" id="recaptcha_token" name="recaptcha_token">
    
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
          
</body>
</html>