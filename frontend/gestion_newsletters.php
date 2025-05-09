<?php
include_once("../db.php");
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 1 && $_SESSION['user']['role'] !== 2)) {
    header("Location: ../frontend/connexion.php");
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
        header("Location: gestion_newsletters.php?success=Newsletter publiée avec succès.");
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


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Newsletters</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css">
</head>
<body>

<header>
    <nav class="custom-navbar">
        <div class="logo-wrapper">
        <a href="
                <?php
                if ($_SESSION['user']['role'] == 1) {
                    echo "/ESPORTIFY/frontend/admin_dashboard.php"; // Admin
                } elseif ($_SESSION['user']['role'] == 2) {
                    echo "/ESPORTIFY/frontend/organisateur_dashboard.php"; // Organisateur
                } else {
                    echo "../frontend/connexion.php"; // Connexion si aucun rôle défini
                }
                ?> ">
                <div class="logo-container">
                    <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                </div>
            </a>
            <div class="semi-circle-outline"></div>
        </div>
    </nav>
</header>

<main>
    <section class="dashboard">
        <h1>Gestion des Newsletters</h1>

        <!-- Formulaire de création de newsletter -->
        <h3>Créer une nouvelle Newsletter</h3>
        <form action="gestion_newsletters.php" method="POST">
            <label for="title">Titre de la newsletter :</label>
            <input type="text" name="title" id="title" required>

            <label for="subject">Sujet de la newsletter :</label>
            <input type="text" name="subject" id="subject" required>

            <label for="content">Contenu de la newsletter :</label>
            <textarea name="message" id="message" rows="10" required></textarea>

            <button type="submit" name="action" value="create_newsletter">Publier</button>
        </form>

        <!-- Affichage des newsletters -->
        <h3>Newsletters publiées</h3>
        <table>
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
                        <td><?= htmlspecialchars($news['author_name']) ?> (<?= $news['role'] == 1 ? 'Admin' : 'Organisateur' ?>)</td>
                        <td>
                            <a href="newsletter_details.php?id=<?= $news['id'] ?>" class="button">Voir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<footer>
    <nav>
        <span>Moussa Mehdi-Guillaume</span>
        <img src="../img/copyrighlogo.jpg" alt="Copyright">
        <ul>
            <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
            <li><a href="#mentions_legales">Mentions légales</a></li>
        </ul>
    </nav>
</footer>

</body>
</html>
