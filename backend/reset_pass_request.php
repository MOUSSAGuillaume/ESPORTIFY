<?php
// Connexion à la base de données
require ('../db.php');

require_once '../../ESPORTIFY/vendor/autoload.php'; // Autoload de PHPMailer

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Vérification que l'email a été soumis
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Vérifier si l'email existe dans la base de données
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Générer un token unique
        $token = bin2hex(random_bytes(50)); // Token de 100 caractères
        $expires = date("U") + 1800; // Le lien expirera dans 30 minutes

        // Enregistrer le token et son expiration dans la base de données
        $query = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sis", $token, $expires, $email);
        $stmt->execute();

        // Lien de réinitialisation
        $server = $_SERVER['HTTP_HOST'];
        $resetLink = "http://localhost/ESPORTIFY/frontend/reset_pass.php?token=" . urlencode($token);


        // Créer l'objet PHPMailers
        $mail = new PHPMailer(true);
        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'monmailtest.dev@gmail.com'; // Remplace par ton adresse Gmail
            $mail->Password = 'wycqaahovznznhba'; // Remplace par ton mot de passe Gmail ou mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinataire et expéditeur
            $mail->setFrom('monmailtest.dev@gmail.com', 'Esportify');
            $mail->addAddress($email);

            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->isHTML(true);
            $mail->Body = "
                <p>Bonjour,</p>
                <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez ci-dessous :</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>Ce lien expirera dans 30 minutes.</p>
            ";

            // Envoyer l'email
            echo "Envoi de l'email...";

            $mail->send();
            echo "Un lien de réinitialisation a été envoyé à votre email.";
        } catch (Exception $e) {
            echo "<pre>";
            echo "Erreur d'envoi : " . $mail->ErrorInfo . "\n";
            echo "Exception : " . $e->getMessage() . "\n";
            print_r(error_get_last());
            echo "</pre>";
            return false;
        }
    } else {
        echo "Cet email n'est pas associé à un compte.";
    }
}
?>
