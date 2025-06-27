<?php
// Démarre la session pour accéder aux variables $_SESSION
session_start();

// Inclusion de la connexion à la base de données
include_once(__DIR__ . '/../db.php');

// Chargement des dépendances de PHPMailer
require_once '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '/vendor/autoload.php'; // Autoload de PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['new_email'];
    $user_id = $_SESSION['user_id'];  // Récupération de l'ID utilisateur depuis la session

    // Vérification si l'email existe déjà dans la base de données
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $new_email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo "❌ Cet email est déjà utilisé.";
        exit;
    }

    // Validation de l'email (vérification de la syntaxe)
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Adresse email invalide.";
        exit;
    }

    // Génération d'un token unique pour la confirmation de l'email
    $token = bin2hex(random_bytes(50)); // Création d'un token unique pour valider l'email

    // Mise à jour de l'utilisateur avec le nouveau token pour la confirmation
    $sql = "UPDATE users SET token = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $token, $user_id);

    if ($stmt->execute()) {
        // Envoi d'un email de confirmation à la nouvelle adresse
        $mail = new PHPMailer(true);
        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de Gmail
            $mail->SMTPAuth = true; // Authentification SMTP
            $mail->Username = $_ENV['SMTP_USER_2']; // Utilisation d'un email d'expéditeur
            $mail->Password = $_ENV['SMTP_PASS_2']; // Mot de passe ou mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Port SMTP pour TLS

            // Récupération de l'URL de base depuis les variables d'environnement
            $base_url = $_ENV['BASE_URL'] ?? 'http://localhost/ESPORTIFY';

            // Configuration de l'email
            $mail->setFrom($_ENV['SMTP_USER_2'], 'Esportify'); // Adresse email de l'expéditeur
            $mail->addAddress($new_email); // Adresse email du destinataire
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de modification de votre email';
            $mail->Body = '<p>Bonjour,</p>
                           <p>Vous avez demandé à changer votre adresse email sur Esportify.</p>
                           <p>Pour confirmer cette modification, cliquez sur le lien suivant : <a href="' . $base_url . '/backend/confirm_email_change.php?token=' . urlencode($token) . '">Confirmer la modification de mon email</a></p>';

            // Envoi de l'email
            $mail->send();
            echo 'Un email de confirmation a été envoyé à votre nouvelle adresse.';
        } catch (Exception $e) {
            echo "❌ L'email n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
        }
    } else {
        echo '❌ Erreur lors de la mise à jour du token.';
    }
}