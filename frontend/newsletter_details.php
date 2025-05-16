<?php
include_once("../db.php");

if (isset($_GET['id'])) {
    $newsletterId = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM newsletters WHERE id = ?");
    $stmt->bind_param("i", $newsletterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $newsletter = $result->fetch_assoc();

    if (!$newsletter) {
        echo "Newsletter non trouvée.";
        exit;
    }
} else {
    echo "ID de newsletter manquant.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail de la Newsletter</title>
</head>
<body>
    <h1><?= htmlspecialchars($newsletter['title']) ?></h1>
    <p><strong>Date :</strong> <?= htmlspecialchars($newsletter['created_at']) ?></p>
    <div>
        <?= nl2br(htmlspecialchars($newsletter['message'])) ?>
    </div>
    <a href="https://esportify.alwaysdata.net/frontend/gestion_newsletters.php" class="button">Retour</a>
</body>
</html>
