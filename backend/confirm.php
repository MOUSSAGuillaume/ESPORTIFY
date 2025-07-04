<?php
// Chargement des variables d'environnement
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connexion à la base de données
include_once(__DIR__ . '/../db.php');

// Vérifie si un token est passé dans l'URL
if (!isset($_GET['token']) || !ctype_xdigit($_GET['token'])) {
    echo '❌ Lien invalide ou incomplet.';
    exit;
}

$token = $_GET['token'];

// Vérifier si le token existe
$sql = "SELECT id, actif FROM users WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user['actif'] == 1) {
        echo '✅ Votre compte est déjà activé.';
        exit;
    }

    // Activer le compte
    $sql = "UPDATE users SET actif = 1, token = NULL, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['id']);

    if ($stmt->execute()) {
        // Redirection après succès
        $redirectUrl = $_ENV['BASE_URL'] . '/connexion?success=1';
        header("Location: $redirectUrl");
        exit;
    } else {
        echo '❌ Erreur lors de l’activation du compte.';
    }
} else {
    echo '⚠️ Lien invalide ou déjà utilisé.';
}
