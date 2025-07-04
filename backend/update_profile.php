<?php
session_start();
include_once(__DIR__ . '/../db.php'); // Connexion DB

// Vérification utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Récupération de l'utilisateur
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = !empty($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : $user['username'];
    $avatar = $user['avatar']; // Valeur par défaut = ancienne image

    // Gestion de l'upload avatar
    if (!empty($_FILES['avatar']['name'])) {
        $avatarFileName = basename($_FILES['avatar']['name']);
        $targetDir = __DIR__ . '/../uploads/avatars/';
        $avatarPathForDB = '/uploads/avatars/' . $avatarFileName;
        $targetFile = $targetDir . $avatarFileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Vérification image
        $check = getimagesize($_FILES['avatar']['tmp_name']);
        if ($check === false) {
            echo "Ce fichier n'est pas une image.";
            exit();
        }

        if ($_FILES['avatar']['size'] > 2000000) {
            echo "L'image est trop grande. Max : 2 Mo.";
            exit();
        }

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Formats autorisés : JPG, JPEG, PNG, GIF.";
            exit();
        }

        // Déplacement du fichier
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatar = $avatarPathForDB;
        } else {
            echo "Erreur lors du téléchargement de l'image.";
            exit();
        }
    }

    // Mise à jour BDD
    $update = $conn->prepare("UPDATE users SET username = ?, avatar = ? WHERE id = ?");
    $update->execute([$username, $avatar, $user_id]);
    echo "Profil mis à jour avec succès.";
    
    // Redirection vers le profil après succès
    header("Location: /profile?success=1");
    exit();

}
    