<?php
require('../db.php');
session_start();

header('Content-Type: application/json');

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => "Vous devez être connecté."]);
    exit;
}

$userId = $_SESSION['user']['id'];

// Récupère les données JSON POST
$input = json_decode(file_get_contents('php://input'), true);

$oldPassword = $input['oldPassword'] ?? '';
$newPassword = $input['newPassword'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';

// Validation simple
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => "Veuillez remplir tous les champs."]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => "Les mots de passe ne correspondent pas."]);
    exit;
}

if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
    echo json_encode(['success' => false, 'message' => "Le nouveau mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre."]);
    exit;
}

// Vérifie l'ancien mot de passe
$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (!password_verify($oldPassword, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => "L'ancien mot de passe est incorrect."]);
        exit;
    }

    // Hash le nouveau mot de passe et met à jour
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $update->bind_param("si", $password_hash, $userId);
    $update->execute();

    echo json_encode(['success' => true, 'message' => "Mot de passe mis à jour avec succès."]);
    exit;
}

    echo json_encode(['success' => false, 'message' => "Utilisateur non trouvé."]);
