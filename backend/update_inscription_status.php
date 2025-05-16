<?php
include_once("../db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription_id'], $_POST['action'])) {
    $id = intval($_POST['inscription_id']);
    $action = $_POST['action'];

    // Sécurité : on limite les actions possibles
    if (!in_array($action, ['confirmé', 'refusé'])) {
        echo "Action non autorisée.";
        exit;
    }

    $stmt = $conn->prepare("UPDATE inscriptions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $id);

    if ($stmt->execute()) {
        echo "✅ Inscription mise à jour : $action";
    } else {
        echo "❌ Erreur : " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Données manquantes.";
}
?>
