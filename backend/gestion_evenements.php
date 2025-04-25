<?php
include_once("../db.php");
session_start();

// Vérification des droits
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Organisateur'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// En-tête JSON
header('Content-Type: application/json');

// Fonctions sécurisées d’échappement
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Validation des données
function validateEventData($title, $description, $event_date) {
    if (empty($title) || empty($description) || empty($event_date)) {
        return 'Tous les champs sont requis.';
    }
    if (strtotime($event_date) < time()) {
        return 'La date de l\'événement ne peut pas être dans le passé.';
    }
    return null;
}

// Gestion des actions
$action = $_GET['action'] ?? null;

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $event_date = $_POST['event_date'];
            $status = $_POST['status'];
            $created_by = $_SESSION['user']['id'];

            // Validation des données
            $error = validateEventData($title, $description, $event_date);
            if ($error) {
                echo json_encode(['error' => $error]);
                exit;
            }

            $sql = "INSERT INTO evenements (title, description, event_date, status, created_by, created_at)
                    VALUES ('$title', '$description', '$event_date', '$status', '$created_by', NOW())";

            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => 'Événement ajouté']);
            } else {
                // Gestion des erreurs SQL
                $error = mysqli_error($conn);
                error_log("Erreur SQL lors de l'ajout de l'événement: $error", 3, 'errors.log');
                echo json_encode(['error' => 'Erreur lors de l\'ajout']);
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $event_date = $_POST['event_date'];
            $status = $_POST['status'];

            // Validation des données
            $error = validateEventData($title, $description, $event_date);
            if ($error) {
                echo json_encode(['error' => $error]);
                exit;
            }

            // Vérifier les permissions : L'Admin peut tout faire, l'Organisateur peut modifier ses propres événements.
            if ($_SESSION['user']['role'] !== 'Admin') {
                // Vérifier que l'événement a été créé par l'utilisateur connecté
                $res = mysqli_query($conn, "SELECT created_by FROM evenements WHERE id = $id");
                $event = mysqli_fetch_assoc($res);
                if ($event['created_by'] !== $_SESSION['user']['id']) {
                    echo json_encode(['error' => 'Vous n\'êtes pas autorisé à modifier cet événement']);
                    exit;
                }
            }

            $sql = "UPDATE evenements SET
                        title = '$title',
                        description = '$description',
                        event_date = '$event_date',
                        status = '$status',
                        updated_at = NOW()
                    WHERE id = $id";

            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => 'Événement mis à jour']);
            } else {
                // Gestion des erreurs SQL
                $error = mysqli_error($conn);
                error_log("Erreur SQL lors de la mise à jour de l'événement: $error", 3, 'errors.log');
                echo json_encode(['error' => 'Erreur lors de la mise à jour']);
            }
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);

            // Vérifier les permissions : L'Admin peut tout faire, l'Organisateur peut supprimer ses propres événements.
            if ($_SESSION['user']['role'] !== 'Admin') {
                // Vérifier que l'événement a été créé par l'utilisateur connecté
                $res = mysqli_query($conn, "SELECT created_by FROM evenements WHERE id = $id");
                $event = mysqli_fetch_assoc($res);
                if ($event['created_by'] !== $_SESSION['user']['id']) {
                    echo json_encode(['error' => 'Vous n\'êtes pas autorisé à supprimer cet événement']);
                    exit;
                }
            }

            $sql = "DELETE FROM evenements WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => 'Événement supprimé']);
            } else {
                // Gestion des erreurs SQL
                $error = mysqli_error($conn);
                error_log("Erreur SQL lors de la suppression de l'événement: $error", 3, 'errors.log');
                echo json_encode(['error' => 'Erreur lors de la suppression']);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Action invalide']);
        break;
}
