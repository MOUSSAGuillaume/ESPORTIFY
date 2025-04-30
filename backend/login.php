<?php
// Sécurisation des cookies de session avant de commencer
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Si vous utilisez HTTPS
ini_set('session.cookie_samesite', 'Strict'); // Empêche les cookies d'être envoyés lors de requêtes cross-site


session_start();  // Démarrer la session après avoir configuré les paramètres

include_once('../db.php'); // Connexion BDD

// Affichage temporaire pour debug (à retirer en prod)
echo '<pre>';
print_r($_POST);
echo '</pre>';

// Vérification des champs email et mot de passe
if (empty($_POST['email']) || empty($_POST['password'])) {
    die('Champs manquants.');
}

$email = $_POST['email'];
$password = $_POST['password'];

// Préparer et exécuter la requête
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // Vérification si le compte est activé
    if ($user['actif'] != 1) {
        header("Location: /ESPORTIFY/frontend/connexion.php?error=2");
        exit;
    }

    // Vérification du mot de passe
    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true); // Sécurité

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'pseudo' => $user['username'],
            'role' => $user['role_id']
        ];

        // Redirection selon le rôle
        switch ($user['role_id']) {
            case 1: // Admin
                header("Location: ../frontend/admin_dashboard.php");
                break;
            case 2: // Organisateurs
                header("Location: ../frontend/organisateur_dashboard.php");
                break;
            case 4: // Joueur
                header("Location: ../frontend/joueur_dashboard.php");
                break;
            default:
                echo "Rôle non reconnu.";
        }
        exit;
    } else {
        echo "Mot de passe incorrect.";
    }
}

// Mauvais identifiants
header("Location: ESPORTIFY/frontend/connexion.php?error=1");
exit;
?>
