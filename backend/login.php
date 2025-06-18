<?php
// Sécurisation des cookies de session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Active seulement si tu es bien en HTTPS
ini_set('session.cookie_samesite', 'Strict');

session_start();

include_once('../db.php');
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Vérification des champs obligatoires
if (empty($_POST['email']) || empty($_POST['password'])) {
    die('Champs manquants.');
}

$email = $_POST['email'];
$password = $_POST['password'];

// Vérification du reCAPTCHA
if (empty($_POST['g-recaptcha-response'])) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=captcha");
    exit();
}

$recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
$recaptchaResponse = $_POST['g-recaptcha-response'];
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
$responseKeys = json_decode($response, true);

if (!$responseKeys["success"]) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=captcha");
    exit();
}

// Requête SQL pour récupérer l'utilisateur
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // Vérifie si le compte est activé
    if ($user['actif'] != 1) {
        header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=2");
        exit();
    }

    // Vérifie le mot de passe
    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'pseudo' => $user['username'],
            'role' => $user['role_id']
        ];

        // Redirige selon le rôle de l'utilisateur
        switch ($user['role_id']) {
            case 1:
                header("Location: https://esportify.alwaysdata.net/frontend/admin_dashboard.php");
                break;
            case 2:
                header("Location: https://esportify.alwaysdata.net/frontend/organisateur_dashboard.php");
                break;
            case 4:
                header("Location: https://esportify.alwaysdata.net/frontend/joueur_dashboard.php");
                break;
            default:
                session_destroy();
                header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=3");
                break;
        }
        exit();
    } else {
        // Mauvais mot de passe
        header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=1");
        exit();
    }
} else {
    // Aucun utilisateur trouvé
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=1");
    exit();
}
?>
