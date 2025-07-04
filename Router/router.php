<?php

function routeRequest() {
    $routes = require __DIR__.'/allRoutes.php'; // array associatif "nomPage" => "chemin"
    $page = $_GET['page'] ?? 'accueil'; // Page par défaut= 'accueil'

    // Vérifie si la page demandée existe dans les routes et si le fichier correspondant existe
    if (isset($routes[$page]) && file_exists(__DIR__ . '/../' . $routes[$page])) {
        return __DIR__ . '/../' . $routes[$page];
    }
    // Si la page n'existe pas, on retourne la page 404
    return __DIR__.'/../frontend/404.php';
}

/*function routeRequest() {
$routes = require __DIR__ . '/allRoutes.php';
$page = $_GET['page'] ?? 'accueil';

// Debug !
var_dump('Clé demandée : ', $page);
var_dump('Clés disponibles : ', array_keys($routes));
var_dump('Valeur recherchée : ', $routes[$page] ?? 'NON TROUVÉ');
// On continue pour test, ou exit ici pour vérifier
// exit;
}
*/