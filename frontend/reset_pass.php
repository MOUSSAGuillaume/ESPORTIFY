<?php
require('../db.php');
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sécurité HTTP
header("Content-Type: text/html; charset=UTF-8");
header("X-Robots-Tag: noindex, nofollow", true);
header("Content-Security-Policy: default-src 'self';");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction de redirection selon le rôle utilisateur
function redirectBasedOnRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: /backend/admin_dashboard.php");
            break;
        case 'organisateur':
            header("Location: /backend/organisateur_dashboard.php");
            break;
        default:
            header("Location: /frontend/profile.php");
            break;
    }
    exit;
}

// Init message pour affichage dans le HTML
$message = null;
$messageType = 'info';

// Récupération du token
$token = $_GET['token'] ?? null;

if (!$token) {
    $message = "❌ Aucun token fourni.";
    $messageType = "error";
} else {
    // Heure serveur MySQL
    $resultTime = $conn->query("SELECT NOW() AS now");
    $timeNow = $resultTime->fetch_assoc()['now'];

    // Vérification du token en BDD
    $stmt = $conn->prepare("
        SELECT u.id, u.email, u.username, r.role_name AS role
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.reset_token = ? AND u.reset_token_expires > ?
    ");
    $stmt->bind_param("ss", $token, $timeNow);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = "❌ Ce lien est invalide ou expiré.";
        $messageType = "error";
    } else {
        $user = $result->fetch_assoc();

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if (empty($newPassword) || empty($confirmPassword)) {
                $message = "❌ Veuillez remplir tous les champs.";
                $messageType = "error";
            } elseif ($newPassword !== $confirmPassword) {
                $message = "❌ Les mots de passe ne correspondent pas.";
                $messageType = "error";
            } elseif (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
                $message = "❌ Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
                $messageType = "error";
            } else {
                // Hash et mise à jour
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $update->bind_param("si", $hashedPassword, $user['id']);
                $update->execute();

                // Connexion automatique
                $fullUserStmt = $conn->prepare("
                    SELECT u.*, r.role_name AS role
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?
        ");
                $fullUserStmt->bind_param("i", $user['id']);
                $fullUserStmt->execute();
                $fullUserResult = $fullUserStmt->get_result();
                $fullUser = $fullUserResult->fetch_assoc();
                $_SESSION['user'] = $fullUser;
                redirectBasedOnRole($fullUser['role']); // ici role = "admin"


                if ($fullUser) {
                    $_SESSION['user'] = $fullUser;
                    redirectBasedOnRole($fullUser['role']); // Redirection ici
                } else {
                    $message = "Une erreur est survenue après la mise à jour du mot de passe.";
                    $messageType = "error";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        form {
            background: white;
            padding: 20px;
            margin: auto;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label, input {
            display: block;
            width: 100%;
            margin-bottom: 12px;
        }
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .message {
            margin: 20px auto;
            max-width: 400px;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<?php if ($message): ?>
    <div class="message <?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST">
    <label for="newPassword">Nouveau mot de passe :</label>
    <input type="password" name="newPassword" id="newPassword" required>

    <label for="confirmPassword">Confirmer le mot de passe :</label>
    <input type="password" name="confirmPassword" id="confirmPassword" required>

    <button type="submit">Réinitialiser le mot de passe</button>
</form>

</body>
</html>
