<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();// indispensable pour lire $_SESSION
}
require_once '../backend/fonctions.php'; // fonction checkCsrf()
include_once("../db.php");

if (isset($_POST['like_actualite'])) {
    checkCsrf(); // CSRF protection

    $id_actu = (int) $_POST['id_actualite_like'];

    if (isset($_SESSION['user']['id'])) {
        $id_joueur = $_SESSION['user']['id'];

        // Préparation sécurisée
        $stmt = mysqli_prepare($conn, "SELECT * FROM likes_actualites WHERE id_actualite = ? AND id_joueur = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id_actu, $id_joueur);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 0) {
            $insert = mysqli_prepare($conn, "INSERT INTO likes_actualites (id_actualite, id_joueur) VALUES (?, ?)");
            mysqli_stmt_bind_param($insert, "ii", $id_actu, $id_joueur);
            mysqli_stmt_execute($insert);
        } else {
            $delete = mysqli_prepare($conn, "DELETE FROM likes_actualites WHERE id_actualite = ? AND id_joueur = ?");
            mysqli_stmt_bind_param($delete, "ii", $id_actu, $id_joueur);
            mysqli_stmt_execute($delete);
        }
    } else {
        echo "Erreur : utilisateur non connecté.";
    }
}

