<?php

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once(__DIR__ . '/../db.php');

$pageTitle = "Contact | Esportify";
$pageDescription = "Contact-nous en cas de problème ou d'incident.";
?>

<link rel="stylesheet" href="../css/contact.css">

<section class="container my-5">
    <h1 class="text-center">Contact Us</h1>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <form method="POST" action="https://esportify.alwaysdata.net/backend/send_contact.php" id="contact-form" class="boxrdv">
                <div class>
                    <label for="email" class="form-label">Email :</label>
                    <input type="email" id="email" name="email" class="form-control" required />
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