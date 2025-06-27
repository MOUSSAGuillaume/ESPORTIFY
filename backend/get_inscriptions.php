<?php
include_once(__DIR__ . '/../db.php');

if (!isset($_GET['event_id'])) {
    echo "ID événement manquant.";
    exit;
}

$eventId = intval($_GET['event_id']);

$query = "SELECT i.id AS inscription_id, u.username, u.email, i.status
          FROM inscriptions i
          JOIN users u ON i.user_id = u.id
          WHERE i.event_id = $eventId";

$result = mysqli_query($conn, $query);

if (!$result) {
    // Si la requête échoue, on affiche l'erreur
    echo "Erreur dans la requête SQL : " . mysqli_error($conn);
    exit;
}

if (mysqli_num_rows($result) === 0) {
    echo "Aucune inscription pour ce tournoi.";
    exit;
}

echo "<table>";
echo "<tr><th>Nom</th><th>Email</th><th>Statut</th><th>Actions</th></tr>";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Ton code pour afficher les données
        echo "<tr data-id='" . $row['inscription_id'] . "'>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td class='actions-cell'>
                    <div style='display: flex; gap: 8px;'>
                    <button class='valider-inscription' data-id='" . $row['inscription_id'] . "' style='padding: 6px 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>Valider</button>
                    <button class='refuser-inscription' data-id='" . $row['inscription_id'] . "' style='padding: 6px 12px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;'>Refuser</button>
                </div>
            </td>";
        echo "</tr>";
    }
} else {
    echo "Aucune inscription pour ce tournoi.";
}
