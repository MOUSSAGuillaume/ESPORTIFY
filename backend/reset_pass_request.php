<?php
session_start();

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

include_once(__DIR__ . '/../db.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Empêche l'indexation par les moteurs de recherche
header("X-Robots-Tag: noindex, nofollow", true);

// En-tête JSON si appelé via JS (optionnel)
// header('Content-Type: application/json');

// Récupère l'email du formulaire
$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Adresse email invalide.";
    exit;
}

// Rechercher l'utilisateur correspondant à l'email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // On ne précise pas si l'email existe ou non
    echo "✅ Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Génère un token sécurisé et une date d'expiration (30 minutes)
$token = bin2hex(random_bytes(50));
$expiresAt = date('Y-m-d H:i:s', time() + 1800);

// Enregistre le token et l’expiration en base
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$update->bind_param("ssi", $token, $expiresAt, $userId);
$update->execute();

// Construit l’URL vers la page de réinitialisation
$baseUrl = rtrim($_ENV['BASE_URL'] ?? 'https://esportify.alwaysdata.net/', '/');
$resetLink = $baseUrl . '/frontend/reset_pass.php?token=' . urlencode($token);

// Envoie l’email avec PHPMailer
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

    $mail->Body = "
        <p>Bonjour,</p>
        <p>Vous avez demandé à réinitialiser le mot de passe pour votre compte <strong>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</strong>.</p>
        <p>Cliquez sur le lien suivant pour choisir un nouveau mot de passe :</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p><em>Ce lien est valide pendant 30 minutes.</em></p>
        <p>Si vous n'avez pas fait cette demande, ignorez simplement ce message.</p>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Erreur envoi email : " . $mail->ErrorInfo);
    // Pas de message d'erreur affiché à l'utilisateur
}

// Réponse neutre pour l'utilisateur
echo "✅ Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";
