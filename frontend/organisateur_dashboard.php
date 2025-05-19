<?php

include_once("../db.php");
session_start();

// Authentification
if (!isset($_SESSION['user'])) {
    header("Location: https://esportify.alwaysdata.net/frontend/connexion.php
");
    exit;
}
if ($_SESSION['user']['role'] !== 2) {
    header("Location: https://esportify.alwaysdata.net/frontend/accueil.php
");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['user']['pseudo'];
$id_admin = $_SESSION['user']['id'];

// Traitement du formulaire de commentaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['poster_commentaire']) && isset($_POST['commentaire'], $_POST['id_newsletter'])) {
    $commentaire = mysqli_real_escape_string($conn, $_POST['commentaire']);
    $id_newsletter = (int)$_POST['id_newsletter'];
    $now = date("Y-m-d H:i:s");

    // Insertion du commentaire
    $stmt = $conn->prepare("INSERT INTO commentaires_newsletters (id_newsletter, id_user, commentaire, date_commentaire) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_newsletter, $id_admin, $commentaire, $now);
    $stmt->execute();
    $stmt->close();
}

// Traitement du formulaire de réponse
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reponse']) && isset($_POST['id_commentaire'])) {
    $reponse = mysqli_real_escape_string($conn, $_POST['reponse']);
    $id_commentaire = (int)$_POST['id_commentaire'];
    $now = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO reponses_commentaires (id_commentaire, id_joueur, reponse, date_reponse) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_commentaire, $id_admin, $reponse, $now);
    $stmt->execute();
    $stmt->close();
}

// Récupérer les newsletters
$newsQuery = mysqli_query($conn, "
    SELECT n.id, n.subject, n.message, n.created_at, u.username AS author_name, u.role_id
    FROM newsletters n
    JOIN users u ON n.created_by = u.id
    ORDER BY n.created_at DESC
    LIMIT 5
");

if (!$newsQuery) {
    die("Erreur SQL : " . mysqli_error($conn));
}
$news = [];
while ($row = mysqli_fetch_assoc($newsQuery)) {
    $news[] = $row;
}

// Events
$tournoisQuery = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
$tournois = [];
while ($row = mysqli_fetch_assoc($tournoisQuery)) {
    $tournois[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Esportify - Organisateur</title>
        <link rel="stylesheet" href="https://esportify.alwaysdata.net/style/dashboard.css"/>
</head>
<body>

 <!-- Effet console -->
    <div class="console-overlay" id="console-overlay">
    <div class="console-text" id="console-text"></div>
    </div>

<main id="dashboard-content">
    <header>
        <nav class="custom-navbar">
            <div class="logo-wrapper">
                <a href="https://esportify.alwaysdata.net/frontend/organisateur_dashboard.php">
                    <div class="logo-container">
                        <img src="../img/logo.png" alt="Esportify Logo" class="logo" />
                    </div>
                </a>
            </div>
        </nav>
    </header>

    <section class="dashboard">
        <h1>Bienvenue Organisateur, <?= htmlspecialchars($username) ?> 🛡️</h1>
        <div class="dashboard-links">
            <a href="https://esportify.alwaysdata.net/frontend/organisateur_gestion.php" class="btn">Gestion des Events</a>
            <!--<a href="/ESPORTIFY/frontend/gestion_utilisateurs.php" class="btn">Gérer les utilisateurs</a>-->
            <a href="https://esportify.alwaysdata.net/frontend/gestion_newsletters.php" class="btn">Gestion des newsletters</a>
            <a href="https://esportify.alwaysdata.net/backend/logout.php" class="btn btn-danger">Déconnexion</a>
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
                        <em>
                            Publié le <?= date("d/m/Y H:i", strtotime($n['created_at'])) ?>
                            par <?= htmlspecialchars($n['author_name']) ?>
                            (<?= $n['role_id'] == 1 ? 'Admin' : 'Organisateur' ?>)
                        </em>


                        <!-- Commentaires -->
                        <?php
                        $id_news = (int)$n['id'];
                        $resCom = mysqli_query($conn,  "SELECT cn.*, u.username
                                                        FROM commentaires_newsletters cn
                                                        JOIN users u ON cn.id_user = u.id
                                                        WHERE cn.id_newsletter = $id_news
                                                        ORDER BY cn.date_commentaire DESC");?>

                        <div class="commentaires-section">
                            <h4>💬 Commentaires :</h4>
                            <form method="post" style="margin-top:10px;">
                                            <input type="hidden" name="id_newsletter" value="<?= $n['id'] ?>">
                                            <textarea name="commentaire" rows="2" required placeholder="Laissez un commentaire..."></textarea>
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="poster_commentaire" class="btn btn-sm">Commenter</button>
                                        </form>
                            <?php if (mysqli_num_rows($resCom) > 0): ?>
                                <?php while ($com = mysqli_fetch_assoc($resCom)) : ?>
                                    <div class="commentaire">
                                        <strong><?= htmlspecialchars($com['username']) ?> :</strong>
                                        <p><?= nl2br(htmlspecialchars($com['commentaire'])) ?></p>
                                        <small>🕒 <?= date("d/m/Y H:i", strtotime($com['date_commentaire'])) ?></small>
                                        <!-- Formulaire pour poster un commentaire -->
                                        


                                        <!-- Réponses -->
                                        <?php
                                        $id_commentaire = $com['id'];
                                        $repQuery = mysqli_query($conn,"SELECT rc.reponse, rc.date_reponse, u.username
                                                                        FROM reponses_commentaires rc
                                                                        LEFT JOIN users u ON rc.id_joueur = u.id
                                                                        WHERE rc.id_commentaire = $id_commentaire
                                                                        ORDER BY rc.date_reponse ASC"); ?>
                                        <div class="reponses">
                                            <?php if (mysqli_num_rows($repQuery) > 0): ?>
                                                <h5>↪️ Réponses :</h5>
                                                <?php while ($rep = mysqli_fetch_assoc($repQuery)) : ?>
                                                    <div class="reponse">
                                                        <strong><?= htmlspecialchars($rep['username'] ?? 'Utilisateur supprimé') ?> :</strong>
                                                        <p><?= nl2br(htmlspecialchars($rep['reponse'])) ?></p>
                                                        <small>🕒 <?= date("d/m/Y H:i", strtotime($rep['date_reponse'])) ?></small>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Formulaire de réponse -->
                                        <form method="post" style="margin-top:10px;">
                                            <input type="hidden" name="id_commentaire" value="<?= $com['id'] ?>">
                                            <textarea name="reponse" rows="2" required placeholder="Votre réponse organisateur..."></textarea>
                                            <button type="submit" class="btn btn-sm">Répondre</button>
                                        </form>
                                    </div>
                                    <hr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <p>Aucun commentaire pour cette actualité.</p>
                            <?php endif; ?>
                        </div>
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
                        <strong><?= htmlspecialchars($t['title']) ?></strong> - <?= date("d/m/Y", strtotime($t['event_date'])) ?> <br>
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

</body>
</html>

<script>
    const consoleText = document.getElementById("console-text");
    const overlay = document.getElementById("console-overlay");
    const dashboard = document.getElementById("dashboard-content");

    const lines = [
        "Connexion au panneau organisateur...",
        "Chargement des actualités...",
        "Chargement des Events...",
        "Vérification des Validations...",
        "Chargement des utilisateurs...",
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
