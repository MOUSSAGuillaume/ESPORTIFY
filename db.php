<?php
$host = 'localhost';
$user = 'root'; // à adapter selon ton serveur
$password = ''; // à adapter aussi
$dbname = 'ESPORTIFY'; // nom de ta base

// Connexion
$conn = mysqli_connect($host, $user, $password, $dbname);

// Vérification
if (!$conn) {
    die("❌ Connexion échouée : " . mysqli_connect_error());
}

// Pour forcer l’UTF-8
mysqli_set_charset($conn, "utf8");
?>
