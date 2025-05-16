<?php
session_start(); // Démarre la session

// Autoload & environnement
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connexion à la base de données
require_once('../db.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "❌ Vous devez être connecté pour réinitialiser votre mot de passe.";
    exit();
}

$email = $_SESSION['user']['email'];

    // Vérification de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Adresse email invalide.";
        exit;
    }

    // Rechercher l'utilisateur
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Génération du token
        $token = bin2hex(random_bytes(50));
        $expires = time() + 1800; // 30 min

        // Mise à jour en base
        $expiresFormatted = date('Y-m-d H:i:s', $expires);
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiresFormatted, $email);
        $update->execute();

        // URL dynamique
        $baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/ESPORTIFY';
        $resetLink = $baseUrl . '/frontend/reset_pass.php?token=' . urlencode($token);

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER_2'];
            $mail->Password = $_ENV['SMTP_PASS_2'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['SMTP_USER_2'], 'Esportify');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = '🔐 Réinitialisation de votre mot de passe';
            $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
                <p>Bonjour,</p>
                <p>Vous avez demandé à réinitialiser le mot de passe pour : <strong>$emailSafe</strong></p>
                <p><a href='$resetLink'>Cliquez ici pour réinitialiser votre mot de passe</a></p>
                <p>Ce lien expirera dans <strong>30 minutes</strong>.</p>
                ";

            $mail->send();
        } catch (Exception $e) {
            // On ignore volontairement l'erreur ici
        }

        // Message toujours affiché, succès ou non
        echo "✅ Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";

    } else {
        echo "✅ Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";
    }