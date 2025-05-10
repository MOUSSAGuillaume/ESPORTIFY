<?php
// Inclure Composer autoload pour charger les dépendances, y compris phpdotenv
require_once __DIR__ . '../../ESPORTIFY/vendor/autoload.php';

// Charger le fichier .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Récupérer les variables d'environnement
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

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

// N'oubliez pas de fermer la connexion à la base de données si vous n'en avez plus besoin
 $conn->close();

