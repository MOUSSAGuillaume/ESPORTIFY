<?php
include_once("../db.php");
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: ../frontend/connexion.php");
    exit;
}

// Vérifie que l'utilisateur a le bon rôle
if ($_SESSION['user']['role'] !== 1) { // Par exemple, 1 pour Admin
    header("Location: ../frontend/accueil.php");
    exit;
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['user']['pseudo'];

// Récupérer les actualités
$newsQuery = mysqli_query($conn, "SELECT sujet AS subject, contenu AS message, created_at FROM newsletters ORDER BY created_at DESC LIMIT 5");
$news = [];
while ($row = mysqli_fetch_assoc($newsQuery)) {
    $news[] = $row;
}

// Récupérer les tournois en cours ou à venir
$tournoisQuery = mysqli_query($conn, "SELECT * FROM tournois WHERE date_tournoi >= CURDATE() ORDER BY date_tournoi ASC");
$tournois = [];
while ($row = mysqli_fetch_assoc($tournoisQuery)) {
    $tournois[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Esportify - Admin</title>
    <link rel="stylesheet" href="/ESPORTIFY/style.css/dashboard_style.css">
</head>
<body>

<div class="console-overlay" id="console-overlay">
    <div class="console-text" id="console-text"></div>
</div>

<main class="hidden" id="dashboard-content">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="/frontend/dashboard_admin.php">
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                    </div>
                </a>
                <div class="semi-circle-outline"></div>
                <a href="/ESPORTIFY/frontend/gestions_utilisateurs.php" class="btn">👥 Gérer les utilisateurs</a>
            </div>
        </nav>
    </header>

    <section class="dashboard">
        <h1>Bienvenue Admin, <?php echo htmlspecialchars($username); ?> 🛡️</h1>
        <div class="dashboard-links">
            <a href="/ESPORTIFY/frontend/gestion_admin.php" class="btn">Gestion admin</a>
            <a href="/ESPORTIFY/backend/logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </section>

    <section class="actualites-section">
        <h2>📰 Fil d'actualité</h2>
        <?php if (!empty($news)) : ?>
            <ul class="news-list">
                <?php foreach ($news as $n) : ?>
                    <li class="news-item">
                        <strong><?= htmlspecialchars($n['subject']) ?></strong><br>
                        <span><?= nl2br(htmlspecialchars($n['message'])) ?></span><br>
                        <em>Publié le <?= date("d/m/Y H:i", strtotime($n['created_at'])) ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucune actualité disponible.</p>
        <?php endif; ?>
    </section>

    <section class="tournois-section">
        <h2>🏆 Tournois en cours / à venir</h2>
        <?php if (!empty($tournois)) : ?>
            <ul class="tournois-list">
                <?php foreach ($tournois as $t) : ?>
                    <li class="tournoi-item">
                        <strong><?= htmlspecialchars($t['titre']) ?></strong> - <?= date("d/m/Y", strtotime($t['date_tournoi'])) ?><br>
                        <em><?= htmlspecialchars($t['description']) ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Aucun tournoi prévu pour l’instant.</p>
        <?php endif; ?>
    </section>

    <footer>
        <nav>
            <span>Moussa Mehdi-Guillaume</span>
            <img src="../img/copyrighlogo.jpg" alt="Copyright" />
            <ul>
                <li><a href="#politique_confidentialite">Politique de confidentialité</a></li>
                <li><a href="#mentions_legales">Mentions légales</a></li>
            </ul>
        </nav>
    </footer>
</main>

<script>
    const consoleText = document.getElementById("console-text");
    const overlay = document.getElementById("console-overlay");
    const dashboard = document.getElementById("dashboard-content");

    const lines = [
        "Connexion au panneau administrateur...",
        "Chargement des actualités...",
        "Chargement des tournois...",
        "Bienvenue sur Esportify 🛡️"
    ];

    let index = 0;
    function typeLine() {
        if (index < lines.length) {
            consoleText.textContent += lines[index] + "\n";
            index++;
            setTimeout(typeLine, 600);
        } else {
            setTimeout(() => {
                overlay.remove();
                const flash = document.createElement("div");
                flash.classList.add("screen-flash");
                document.body.appendChild(flash);
                setTimeout(() => {
                    flash.remove();
                    dashboard.classList.remove("hidden");
                }, 600);
            }, 1000);
        }
    }
    typeLine();
</script>

</body>
</html>
