<?php
$host = 'localhost';
$user = 'root'; // à adapter selon ton serveur
$password = ''; // à adapter aussi
$dbname = 'ESPORTIFY'; // nom de ta base

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
} else {
    echo "Connexion réussie à la base de données !";
}

// Pour forcer l’UTF-8
mysqli_set_charset($conn, "utf8");
