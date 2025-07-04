<?php
// Inclusion de la connexion à la base de données
include_once(__DIR__ . '/../db.php');

// Vérifie que le token est bien fourni dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifie que le token est bien un hexadécimal (sécurité)
    if (!ctype_xdigit($token)) {
        echo '⚠️ Le lien est invalide ou a expiré.';
        exit;
    }

    // Recherche l'utilisateur qui possède ce token ET un pending_email en attente
    $sql = "SELECT id, pending_email FROM users WHERE token = ? AND pending_email IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si le token existe et qu'un utilisateur est trouvé
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $new_email = $user['pending_email'];

        // Mise à jour de l'adresse email, suppression du pending_email et du token
        $update_sql = "UPDATE users SET email = ?, pending_email = NULL, token = NULL WHERE token = ?";
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