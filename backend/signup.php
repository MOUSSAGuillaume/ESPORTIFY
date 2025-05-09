<?php
include_once('../db.php'); // Inclusion de la connexion à la base de données

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/Exception.php';
require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../ESPORTIFY/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../../ESPORTIFY/vendor/autoload.php'; // Autoload de PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'];

    // Vérification de la correspondance des mots de passe
    if ($mot_de_passe !== $confirmer_mot_de_passe) {
        echo '❌ Les mots de passe ne correspondent pas.';
        exit;
    }

    // Vérification de la complexité du mot de passe
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $mot_de_passe)) {
        echo '❌ Le mot de passe ne respecte pas les critères.';
        exit;
    }

    // Vérification si l'email existe déjà dans la base de données
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "❌ Cet email est déjà utilisé.";
        exit;
    }

    // Hachage du mot de passe pour le stockage sécurisé
    $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // Génération d'un token unique pour la confirmation de l'email
    $token = bin2hex(random_bytes(50)); // Création d'un token unique pour valider l'email

    // Récupération de l'ID de rôle (par défaut, un membre)
    $role_id = 4;  // 4 correspond à "joueur" dans la table des rôles

    // Insertion de l'utilisateur dans la base de données
    $sql = "INSERT INTO users (email, username, password_hash, role_id, token, actif) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $email, $username, $password_hash, $role_id, $token);

    if ($stmt->execute()) {
        // Si l'utilisateur est inscrit, on envoie un email de confirmation
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de Gmail
            $mail->SMTPAuth = true; // Authentification SMTP
            $mail->Username = 'monmailtest.dev@gmail.com'; // Utilisation d'un email d'expéditeur
            $mail->Password = 'wycqaahovznznhba'; // Utilisation d'un mot de passe d'email (il faut protéger ces informations dans des variables d'environnement)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Port SMTP pour TLS

            // Configurer l'email
            $mail->setFrom('monmailtest.dev@gmail.com', 'Esportify');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre compte';
            $mail->Body = '<p>Bonjour ' . htmlspecialchars($username) . ',</p><p>Merci de vous être inscrit sur Esportify.
            Cliquez sur le lien suivant pour confirmer votre inscription : <a href="http://localhost/ESPORTIFY/backend/confirm.php?token=' . urlencode($token) . '">
            Confirmer mon compte</a></p>';

            // Envoi de l'email
            $mail->send();
            echo 'success'; // Inscription et email envoyés avec succès
        } catch (Exception $e) {
            echo "❌ L'email n'a pas pu être envoyé. Vérifiez votre configuration SMTP. Erreur : {$mail->ErrorInfo}";
        }
    } else {
        echo '❌ Erreur lors de l\'inscription.';
    }
}
?>
