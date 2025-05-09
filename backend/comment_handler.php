<?php
include_once("../db.php");
if (!$conn) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

if (isset($_POST['poster_commentaire'])) {
    $id_newsletter = (int) $_POST['id_newsletter'];
    $commentaire = mysqli_real_escape_string($conn, $_POST['commentaire']);

    // Assure-toi que l'utilisateur est connecté
    if (isset($_SESSION['user']['id'])) {
        $id_user = $_SESSION['user']['id'];

        $sqlComment = "INSERT INTO commentaires_newsletters (id_newsletter, id_user, commentaire)
                       VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sqlComment);
        mysqli_stmt_bind_param($stmt, "iis", $id_newsletter, $id_user, $commentaire);
        mysqli_stmt_execute($stmt);

        header("Location: ..//frontend/joueur_dashboard.php?id_newsletter=$id_newsletter#commentaires");
        exit;
    } else {
        echo "Erreur : utilisateur non connecté.";
    }
}

// gestion des réponses aux commentaires si tu veux la garder
if (isset($_POST['poster_reponse'])) {
    $id_commentaire = (int) $_POST['id_commentaire'];
    $reponse = mysqli_real_escape_string($conn, $_POST['reponse']);

    if (isset($_SESSION['user']['id'])) {
        $id_user = $_SESSION['user']['id'];

        $sqlReponse = "INSERT INTO reponses_commentaires (id_commentaire, id_user, reponse)
                       VALUES ('$id_commentaire', '$id_user', '$reponse')";
        mysqli_query($conn, $sqlReponse);
    } else {
        echo "Erreur : utilisateur non connecté.";
    }
}