<?php
// Chargement de l'autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connexion à la base de données
require_once __DIR__ . '/../db.php';

// Importation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';

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

    // Vérification si l'email existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "❌ Cet email est déjà utilisé.";
        exit;
    }

    // Hachage du mot de passe
    $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Génération d'un token unique
    $token = bin2hex(random_bytes(50));

    // Rôle par défaut : joueur (id 4)
    $role_id = 4;

    // Insertion en base de données
    $sql = "INSERT INTO users (email, username, password_hash, role_id, token, actif) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $email, $username, $password_hash, $role_id, $token);

    if ($stmt->execute()) {
        // Envoi de l'email de confirmation
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER_2'];
            $mail->Password = $_ENV['SMTP_PASS_2'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['SMTP_USER_2'], 'Esportify'); // Nom de l'expéditeur
            $mail->addAddress($email);  // Adresse du destinataire
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre compte';

            // Utilisation d'une URL dynamique si définie dans .env
            $confirmUrl = 'https://esportify.alwaysdata.net/backend/confirm.php?token=' . urlencode($token);

            $mail->Body = "
                <p>Bonjour " . htmlspecialchars($username) . ",</p>
                <p>Merci de vous être inscrit sur Esportify. Cliquez sur le lien suivant pour confirmer votre inscription :</p>
                <p><a href='{$confirmUrl}'>Confirmer mon compte</a></p>
            ";


            $mail->send();
            echo 'success';
        } catch (Exception $e) {
            echo "❌ L'email n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
        }
    } else {
        echo '❌ Erreur lors de l\'inscription.';
    }
}

