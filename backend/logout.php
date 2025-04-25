<?php
session_start();

// Sauvegarder temporairement le rôle avant la destruction de session
$role = isset($_SESSION['user']['role']) ? strtolower($_SESSION['user']['role']) : null;

// Supprimer les données de session
session_unset();
session_destroy();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


// Redirection personnalisée
switch ($role) {
    case 1:
        header("Location: /ESPORTIFY/frontend/connexion.php?logout=1");
        break;
    default:
        header("Location: /ESPORTIFY/frontend/accueil.php?logout=1");
}
