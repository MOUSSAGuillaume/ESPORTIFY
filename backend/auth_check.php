<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: /connexion");
    exit;
}

// Vérifie que l'utilisateur a le bon rôle
if ($_SESSION['user']['role'] !== 4) { // 4 pour Joueur
    header("Location: /accueil");
    exit;
}

$username = $_SESSION['user']['pseudo'];
$id_joueur = $_SESSION['user']['id'];

