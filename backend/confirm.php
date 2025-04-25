<?php
include_once('../db.php'); // Vérifie bien que ce chemin est correct pour ta structure de projet

// Vérification si le token est fourni dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // Validation basique du format du token
    if (!ctype_xdigit($token))  {
        echo '⚠️ Le lien est invalide ou a expiré.';
        exit;
    }

    // Vérification si le token existe dans la base de données
    $sql = "SELECT id, actif FROM users WHERE token = ?"; // Assure-toi que la table s'appelle 'users' dans ta base de données
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si le token existe dans la base de données
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Vérification si le compte est déjà activé
        if ($user['actif'] == 1) {
            echo '✅ Votre compte est déjà activé.';
        } else {
            // Mise à jour de l'utilisateur pour l'activer et vider le token
            $sql = "UPDATE users SET actif = 1, token = NULL, updated_at = NOW() WHERE token = ?"; // Assure-toi d'utiliser 'users'
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);

            if ($stmt->execute()) {
                // Redirection après activation
                header("Location: ../../ESPORTIFY/frontend/connexion.php?success=1");
                exit;  // Empêcher le reste du code de s'exécuter après la redirection
            } else {
                echo '❌ Une erreur est survenue lors de l’activation. Veuillez réessayer plus tard.';
            }
        }
    } else {
        echo '⚠️ Ce lien est invalide, a expiré ou a déjà été utilisé.';
    }
} else {
    echo '❌ Aucun token fourni. Le lien est peut-être incomplet.';
}
?>
