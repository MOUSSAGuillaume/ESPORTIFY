<?php
// Sécurisation des cookies de session avant de commencer
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Si HTTPS
ini_set('session.cookie_samesite', 'Strict');

session_start();

include_once('../db.php');
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Vérification des champs
if (empty($_POST['email']) || empty($_POST['password'])) {
    die('Champs manquants.');
}

$email = $_POST['email'];
$password = $_POST['password'];

// reCAPTCHA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}

// Préparer et exécuter la requête
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if ($user['actif'] != 1) {
        header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=2");
        exit;
    }

    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'pseudo' => $user['username'],
            'role' => $user['role_id']
        ];

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
        exit;
    } else {
        header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=1");
        exit;
    }
}

// Aucun utilisateur trouvé
header("Location: https://esportify.alwaysdata.net/frontend/connexion.php?error=1");
exit;
