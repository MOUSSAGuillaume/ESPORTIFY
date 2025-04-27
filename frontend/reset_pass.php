<?php
require('../db.php');

require_once '../../ESPORTIFY/vendor/autoload.php'; // Autoload de PHPMailer

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier que le token est valide et pas expiré
    $query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > ?";
    $stmt = $conn->prepare($query);

    $timeNow = time();
    $stmt->bind_param("si", $token, $timeNow);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Le token est valide
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier si les champs sont remplis
            if (!empty($_POST['newPassword']) && !empty($_POST['confirmPassword'])) {
                $newPassword = $_POST['newPassword'];
                $confirmPassword = $_POST['confirmPassword'];

                if ($newPassword === $confirmPassword) {
                    // Hacher et mettre à jour le mot de passe
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    $updateQuery = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("si", $hashedPassword, $token);
                    $updateStmt->execute();

                    echo "<p>✅ Votre mot de passe a été réinitialisé avec succès.</p>";
                } else {
                    echo "<p>❌ Les mots de passe ne correspondent pas.</p>";
                }
            } else {
                echo "<p>❌ Veuillez remplir tous les champs.</p>";
            }
        }
    } else {
        echo "<p>❌ Ce lien de réinitialisation est invalide ou a expiré.</p>";
    }
} else {
    echo "<p>❌ Aucun token fourni.</p>";
}
?>

<!-- Formulaire HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="../style.css"> <!-- Assurez-vous que le chemin est correct -->
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
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
        <input type="password" name="newPassword" required>

        <label for="confirmPassword">Confirmer le mot de passe :</label>
        <input type="password" name="confirmPassword" required>

        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
