<?php
// Charge le fichier autoload de Composer
require 'vendor/autoload.php';

// Crée une instance MongoDB\Client
$client = new MongoDB\Client("mongodb://localhost:27017");

// Accède à une base de données spécifique
$database = $client->esportify;  // Remplace 'esportify' par le nom de ta base de données

// Accède à une collection spécifique
$collection = $database->comments;  // Remplace 'comments' par ta collection de commentaires

// Exemple d'ajout de document
$result = $collection->insertOne([
    'user_id' => 1,
    'article_id' => 123,
    'comment' => 'Ceci est un commentaire.',
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

echo "Commentaire ajouté avec succès !";
