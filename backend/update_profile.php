<?php
session_start();
require_once ('../db.php'); // Connexion DB

// Sécurité : vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}

// Vérification des données envoyées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $avatar = null;

    // Vérifier si l'email existe déjà (autre que l'email de l'utilisateur actuel)
    if ($email !== $user['email']) {
        // Si l'email a changé, on envoie un lien pour confirmer l'email via un autre fichier PHP (change_email_request.php)
        echo "Veuillez confirmer votre nouvelle adresse email via le lien qui vous a été envoyé.";
        exit(); // Ne pas continuer à mettre à jour l'email dans ce fichier
    }

    // Gestion de l'avatar (si un fichier a été téléchargé)
    if (!empty($_FILES['avatar']['name'])) {
        $avatarFileName = basename($_FILES['avatar']['name']);
        $targetDir = "../uploads/avatars/"; // On déplace l'avatar dans un répertoire sécurisé
        $targetFile = $targetDir . $avatarFileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Vérifier que l'image est bien un fichier image
        $check = getimagesize($_FILES['avatar']['tmp_name']);
        if ($check === false) {
            echo "Ce fichier n'est pas une image.";
            exit();
        }

        // Vérifier la taille du fichier (par exemple, max 2 Mo)
        if ($_FILES['avatar']['size'] > 2000000) {
            echo "L'image est trop grande. La taille maximale est de 2 Mo.";
            exit();
        }

        // Accepter certains formats d'image seulement
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            echo "Désolé, seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
            exit();
        }

        // Déplacer le fichier téléchargé dans le répertoire de destination
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatar = $targetFile;
        } else {
            echo "Désolé, une erreur est survenue lors du téléchargement de l'avatar.";
            exit();
        }
    }

    // Mise à jour dans la base de données
    $updateQuery = $conn->prepare("UPDATE users SET username = ?, avatar = ? WHERE id = ?");
    $updateQuery->execute([$username, $avatar, $user_id]);

    echo "Votre profil a été mis à jour avec succès.";
}
?>
