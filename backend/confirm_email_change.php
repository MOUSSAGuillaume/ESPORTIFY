<?php
include_once('../db.php'); // Inclusion de la connexion à la base de données

// Vérification si le token est fourni dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validation basique du format du token
    if (!ctype_xdigit($token)) {
        echo '⚠️ Le lien est invalide ou a expiré.';
        exit;
    }

    // Vérification si le token existe dans la base de données
    $sql = "SELECT id, email FROM users WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si le token existe dans la base de données
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Mise à jour de l'email de l'utilisateur et nettoyage du token
        $new_email = $user['email'];
        $update_sql = "UPDATE users SET email = ?, token = NULL WHERE token = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $new_email, $token);

        if ($update_stmt->execute()) {
            echo '✅ Votre email a été modifié avec succès.';
        } else {
            echo '❌ Une erreur est survenue lors de la mise à jour de votre email.';
        }
    } else {
        echo '⚠️ Ce lien est invalide ou a expiré.';
    }
} else {
    echo '❌ Aucun token fourni. Le lien est peut-être incomplet.';
}
