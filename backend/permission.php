<?php
session_start();

// Imaginons que tu as stocké l'utilisateur en session après login
$role = $_SESSION['user']['role'];

if ($role === 'admin') {
    echo "Accès admin autorisé.";
} elseif ($role === 'organisateur') {
    echo "Accès organisateur autorisé.";
} elseif ($role === 'joueur') {
    echo "Accès joueur autorisé.";
}

if ($role === 'admin') {
    // Montrer tous les boutons d'administration
}

if ($role === 'organisateur') {
    // Affiche les boutons pour gérer les participants
}

if ($role === 'joueur') {
    // Formulaire de proposition d'événement
    // Zone de commentaire sur les newsletters
}
