<?php
// Inclure Composer autoload pour charger les dépendances, y compris phpdotenv
require_once __DIR__ . '/vendor/autoload.php';

// Charger le fichier .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Vérifier si les variables d'environnement existent
if (!isset($_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME'])) {
    die("Erreur : Les variables d'environnement sont manquantes.");
}

// Récupérer les variables d'environnement
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$charset = 'utf8mb4'; // Charset à utiliser pour la connexion

// Créer une connexion à la base de données avec mysqli
$conn = new mysqli($host, $user, $password, $dbname, $port);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
} else {
    echo "Connexion réussie à la base de données !";
}

// Forcer l’utilisation de l’UTF-8 pour la connexion
mysqli_set_charset($conn, "utf8");

// Exemple : exécuter une requête
// $result = $conn->query("SELECT * FROM ma_table");
// while ($row = $result->fetch_assoc()) {
//    echo $row['colonne'] . '<br>';
// }

// N'oubliez pas de fermer la connexion à la base de données si vous n'en avez plus besoin
$conn->close(); // Utilise cette ligne si tu as terminé avec la connexion

