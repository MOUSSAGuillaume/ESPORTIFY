<?php
include_once(__DIR__ . '/../db.php');

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
    <title>Détail de la Newsletter</title>

    <h1 style="color: whitesmoke;"><?= htmlspecialchars($newsletter['title']) ?></h1>
    <p style="color: #c08c3f;"><strong>Date :</strong> <?= htmlspecialchars($newsletter['created_at']) ?></p>
    <div style= "color:wheat;">
        <?= nl2br(htmlspecialchars($newsletter['message'])) ?>
    </div>
    <a href="/gestion_newsletters" class="button">Retour</a>