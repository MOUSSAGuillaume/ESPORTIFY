<?php
// Autoload Composer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Si la requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation des champs
    if (empty($email) || empty($message)) {
        echo "❌ Tous les champs doivent être remplis.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ L'adresse e-mail est invalide.";
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuration de l'expéditeur et du destinataire
        $mail->setFrom($email);
        $mail->addAddress($_ENV['MAIL_RECEIVER']);
        $mail->addReplyTo($email);

        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = '📩 Nouveau message de contact';
        $mail->Body = '
            <h3>📥 Nouveau message reçu via le formulaire</h3>
            <p><strong>Email de l\'expéditeur :</strong> ' . htmlspecialchars($email) . '</p>
            <p><strong>Message :</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>
        ';

        $mail->send();
        echo '✅ Votre message a bien été envoyé !';
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'envoi du message : {$mail->ErrorInfo}";
    }
}
