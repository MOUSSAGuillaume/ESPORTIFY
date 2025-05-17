<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/fonctions.php'; // checkCsrf()
include_once("../db.php");

if (!$conn) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

// Poster un commentaire
if (isset($_POST['poster_commentaire'])) {
    checkCsrf();

    if (!isset($_POST['id_newsletter'], $_POST['commentaire']) || empty(trim($_POST['commentaire']))) {
        die("Erreur : commentaire vide ou champ manquant.");
    }

    $id_newsletter = (int) $_POST['id_newsletter'];
    $commentaire = trim($_POST['commentaire']);

    if (isset($_SESSION['user']['id'])) {
        $id_user = $_SESSION['user']['id'];

        $stmt = mysqli_prepare($conn, "INSERT INTO commentaires_newsletters (id_newsletter, id_user, commentaire) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $id_newsletter, $id_user, $commentaire);
        mysqli_stmt_execute($stmt);

        // Redirection après envoi
        header("Location: https://esportify.alwaysdata.net/frontend/joueur_dashboard.php?id_newsletter=$id_newsletter#commentaires");
        exit;
    } else {
        echo "Erreur : utilisateur non connecté.";
    }
}

// Poster une réponse à un commentaire
if (isset($_POST['poster_reponse'])) {
    checkCsrf();

    if (!isset($_POST['id_commentaire'], $_POST['reponse']) || empty(trim($_POST['reponse']))) {
        die("Erreur : réponse vide ou champ manquant.");
    }

    $id_commentaire = (int) $_POST['id_commentaire'];
    $reponse = trim($_POST['reponse']);

    if (isset($_SESSION['user']['id'])) {
        $id_user = $_SESSION['user']['id'];

        $stmt = mysqli_prepare($conn, "INSERT INTO reponses_commentaires (id_commentaire, id_user, reponse) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $id_commentaire, $id_user, $reponse);
        mysqli_stmt_execute($stmt);
    } else {
        echo "Erreur : utilisateur non connecté.";
    }
}
