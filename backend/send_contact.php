<?php
include_once("../db.php");
require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/Exception.php';
require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    // Validation des champs
    if (empty($email) || empty($message)) {
        echo "❌ Tous les champs doivent être remplis.";
        exit;
    }

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ L'email est invalide.";
        exit;
    }

    // Créer une nouvelle instance de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configurer PHPMailer pour utiliser SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Exemple : utiliser Gmail comme SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'moussaguillaume.dev@gmail.com';  // Ton adresse email pour l'envoi
        $mail->Password = 'wjsixwqbvyyqshnu';  // Mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configurer l'email
        $mail->setFrom($email);  // L'email de l'utilisateur est utilisé comme expéditeur
        $mail->addAddress('moussaguillaume.dev@gmail.com');  // L'adresse email où tu veux recevoir les messages
        //repondre au message du formulaire
        $mail->addReplyTo($email);  // Répondre à l'email de l'utilisateur
        

        $mail->isHTML(true);  // Le message sera au format HTML
        $mail->Subject = 'Nouveau message de contact';
        $mail->Body = '<p><strong>Email:</strong> ' . $email . '</p>
                       <p><strong>Message:</strong> ' . nl2br($message) . '</p>';

        // Envoi de l'email
        $mail->send();
        echo '✅ Votre message a bien été envoyé !';
    } catch (Exception $e) {
        echo "❌ L'email n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
    }
}
?>
