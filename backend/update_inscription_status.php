<?php
include_once("../db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription_id'], $_POST['action'])) {
    $id = intval($_POST['inscription_id']);
    $action = $_POST['action']; // 'confirmé' ou 'refusé'

    // Protection contre les valeurs inattendues
    if (!in_array($action, ['confirmé', 'refusé'])) {
        echo "Action non autorisée.";
        exit;
    }

    $query = "UPDATE inscriptions SET status = '$action' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "Inscription mise à jour : $action";
    } else {
        echo "Erreur : " . mysqli_error($conn);
    }
} else {
    echo "Données manquantes.";
}
