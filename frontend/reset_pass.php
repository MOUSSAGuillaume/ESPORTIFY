<?php
require('../db.php');
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function afficherMessage($message, $type = "info") {
    $styles = [
        "success" => "color: green;",
        "error"   => "color: red;",
        "info"    => "color: #333;"
    ];
    echo "<p style='{$styles[$type]}'>$message</p>";
}

$token = $_GET['token'] ?? null;

if (!$token) {
    afficherMessage("❌ Aucun token fourni.", "error");
    exit;
}

afficherMessage("Token reçu : <strong>" . htmlspecialchars($token) . "</strong>", "info");

// Récupérer l'heure actuelle via MySQL
$resultTime = $conn->query("SELECT NOW() AS now");
$timeNow = $resultTime->fetch_assoc()['now'];
afficherMessage("Heure serveur MySQL : <strong>$timeNow</strong>", "info");

// Vérifier que le token est valide et non expiré
$query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $token, $timeNow);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    afficherMessage("❌ Ce lien de réinitialisation est invalide ou a expiré.", "error");
    exit;
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        afficherMessage("❌ Veuillez remplir tous les champs.", "error");
    } elseif ($newPassword !== $confirmPassword) {
        afficherMessage("❌ Les mots de passe ne correspondent pas.", "error");
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateQuery = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ss", $hashedPassword, $token);
        $updateStmt->execute();

        afficherMessage("✅ Votre mot de passe a été réinitialisé avec succès.", "success");
    }
}
?>

<!-- Formulaire HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            max-width: 400px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<form method="POST">
    <label for="newPassword">Nouveau mot de passe :</label>
    <input type="password" name="newPassword" id="newPassword" required>

    <label for="confirmPassword">Confirmer le mot de passe :</label>
    <input type="password" name="confirmPassword" id="confirmPassword" required>

    <button type="submit">Réinitialiser le mot de passe</button>
</form>

</body>
</html>
