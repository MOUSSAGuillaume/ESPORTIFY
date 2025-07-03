<?php
include_once(__DIR__ . '/../db.php');

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 1 && $_SESSION['user']['role'] !== 2)) {
    header("Location: /index.php?page=connexion");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_newsletter') {
    // Validation des champs
    if (empty($_POST['title']) || empty($_POST['subject']) || empty($_POST['message'])) {
        die("Tous les champs sont obligatoires.");
    }

    // Assainir et sécuriser les données
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insertion de la newsletter dans la base de données
    $stmt = $conn->prepare("INSERT INTO newsletters (title, subject, message, created_at, created_by) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("sss", $title, $subject, $message);

    if ($stmt->execute()) {
        header("Location: /gestion_newsletters?success=Newsletter publiée avec succès.");
        exit;
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Récupération des newsletters
$newsletters = $conn->query("
    SELECT n.*, u.username AS author_name, u.role_id AS role
    FROM newsletters n
    JOIN users u ON n.created_by = u.id
    ORDER BY n.created_at DESC
");
?>

<link rel="stylesheet" href="../css/dashboard.css">

<div class="container py-5">
    <div class="mb-3">
        <a href="<?php
                    if ($_SESSION['user']['role'] == 1) {
                        echo "/admin_dashboard";
                    } elseif ($_SESSION['user']['role'] == 2) {
                        echo "/organisateur_dashboard";
                    } else {
                        echo "/connexion";
                    }
                    ?>" class="btn btn-outline-info rounded-pill fw-bold">
            Retour Dashboard
        </a>
    </div>
    <section class="mb-4">
        <h2 class="card-title mb-3">Gestion des Newsletters</h2>
    </section>

    <div class="news-item shadow-lg mb-4">
        <div class="card-body">
            <!-- Formulaire création newsletter -->
            <h4 class="mb-3">Créer une nouvelle Newsletter</h4>
            <form action="/gestion_newsletters" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Titre de la newsletter :</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="subject" class="form-label">Sujet de la newsletter :</label>
                    <input type="text" name="subject" id="subject" class="form-control" required>
                </div>
                <div class="col-12">
                    <label for="message" class="form-label">Contenu de la newsletter :</label>
                    <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" name="action" value="create_newsletter" class="btn btn-primary rounded-pill px-4">
                        Publier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Affichage des newsletters -->
    <div class="news-item">
        <div class="card-body">
            <h4 class="card-title mb-3">Newsletters publiées</h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover align-middle rounded-3 overflow-hidden">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Extrait</th>
                            <th>Publié par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($news = mysqli_fetch_assoc($newsletters)): ?>
                            <tr>
                                <td><?= htmlspecialchars($news['title']) ?></td>
                                <td><?= htmlspecialchars($news['created_at']) ?></td>
                                <td><?= substr(htmlspecialchars($news['subject']), 0, 80) . '...' ?></td>
                                <td>
                                    <?= htmlspecialchars($news['author_name']) ?>
                                    (<?= $news['role'] == 1 ? 'Admin' : 'Organisateur' ?>)
                                </td>
                                <td>
                                    <a href="/newsletter_details?id=<?= $news['id'] ?>" class="btn btn-outline-info btn-sm rounded-pill">Voir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>