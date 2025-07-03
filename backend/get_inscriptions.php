<?php
include_once(__DIR__ . '/../db.php');

if (!isset($_GET['event_id'])) {
    echo "<div class='alert alert-danger'>ID événement manquant.</div>";
    exit;
}

$eventId = intval($_GET['event_id']);

$query = "SELECT i.id AS inscription_id, u.username, u.email, i.status
          FROM inscriptions i
          JOIN users u ON i.user_id = u.id
          WHERE i.event_id = $eventId";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "<div class='alert alert-danger'>Erreur dans la requête SQL : " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    exit;
}

if (mysqli_num_rows($result) === 0) {
    echo "<div class='alert alert-info'>Aucune inscription pour ce tournoi.</div>";
    exit;
}

echo "<div class='table-responsive'>";
echo "<table class='table table-bordered align-middle mb-0'>";
echo "<thead class='table-dark'>";
echo "<tr>
        <th>Nom</th>
        <th>Email</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    // Badge pour le statut
    $badge = '<span class="badge bg-secondary">'.htmlspecialchars($row['status']).'</span>';
    if (strtolower($row['status']) === 'confirmé' || strtolower($row['status']) === 'confirmé' || strtolower($row['status']) === 'validé') {
        $badge = '<span class="badge bg-success">Confirmé</span>';
    }
    elseif (strtolower($row['status']) === 'refusé') {
        $badge = '<span class="badge bg-danger">Refusé</span>';
    }
    elseif (strtolower($row['status']) === 'en attente') {
        $badge = '<span class="badge bg-warning text-dark">En attente</span>';
    }

    echo "<tr data-id='" . $row['inscription_id'] . "'>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . $badge . "</td>";
    echo "<td class='actions-cell'>
            <div class='d-flex gap-1'>
                <button class='btn btn-success btn-sm valider-inscription' data-id='" . $row['inscription_id'] . "'>Valider</button>
                <button class='btn btn-danger btn-sm refuser-inscription' data-id='" . $row['inscription_id'] . "'>Refuser</button>
            </div>
          </td>";
    echo "</tr>";
}

echo "</tbody></table></div>";
?>
