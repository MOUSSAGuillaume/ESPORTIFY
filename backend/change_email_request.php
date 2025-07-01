<?php
// On démarre la session pour récupérer les infos utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
include_once(__DIR__ . '/../db.php');

// Inclusion de l'autoloader PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement des variables d'environnement (pour les infos SMTP)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Si le formulaire a été soumis (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération de la nouvelle adresse email et de l'id utilisateur depuis la session
    $new_email = $_POST['new_email'] ?? '';
    $user_id = $_SESSION['user']['id'] ?? null;

    // Vérifie que l'utilisateur est bien connecté
    if (!$user_id) {
        echo "❌ Utilisateur non authentifié.";
        exit;
    }

    // Vérifie si l'email existe déjà (dans email OU dans pending_email d'un autre utilisateur)
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR pending_email = ?");
    $check->bind_param("ss", $new_email, $new_email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "❌ Cet email est déjà utilisé ou en cours de validation.";
        exit;
    }

    // Vérifie que l'adresse est valide
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Adresse email invalide.";
        exit;
    }

    // Génère un token unique (pour le lien de validation)
    $token = bin2hex(random_bytes(50));

    // Enregistre le token ET la nouvelle adresse (pending_email) dans la base
    $sql = "UPDATE users SET pending_email = ?, token = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_email, $token, $user_id);

    if ($stmt->execute()) {
        // Envoie un mail de validation à la nouvelle adresse
        $mail = new PHPMailer(true);
        try {
            // Paramètres SMTP (Gmail ici, adapte si besoin)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER_2'];
            $mail->Password = $_ENV['SMTP_PASS_2'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // URL de base pour le lien de validation
            $base_url = $_ENV['BASE_URL'] ?? 'http://localhost/ESPORTIFY';

            // Construction de l'e-mail
            $mail->setFrom($_ENV['SMTP_USER_2'], 'Esportify');
            $mail->addAddress($new_email);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de modification de votre email';
            $mail->Body = '<p>Bonjour,</p>
                <p>Vous avez demandé à changer votre adresse email sur Esportify.</p>
                <p>Pour confirmer cette modification, cliquez sur le lien suivant :<br>
                <a href="' . $base_url . '/backend/confirm_email_change.php?token=' . urlencode($token) . '">Confirmer la modification de mon email</a></p>';

            $mail->send();
            echo 'Un email de confirmation a été envoyé à votre nouvelle adresse.';
        } catch (Exception $e) {
            echo "❌ L'email n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
        }
    } else {
        echo '❌ Erreur lors de la mise à jour de votre demande.';
    }
} else {
    // Affiche un petit formulaire simple si la page est chargée en GET
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title>Changer mon email</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    </head>

    <body class="bg-dark text-light">
        <div class="container mt-5">
            <h2>Changer mon adresse email</h2>
            <form method="POST" class="my-4" style="max-width: 420px;">
                <div class="mb-3">
                    <label for="new_email" class="form-label">Nouvel email</label>
                    <input type="email" name="new_email" id="new_email" class="form-control" required placeholder="nouveau@email.com">
                </div>
                <button type="submit" class="btn btn-primary">Valider</button>
            </form>
        </div>
    </body>

    </html>
<?php
}