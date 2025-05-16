<?php
include_once("../db.php");
session_start();

// Vérification des droits
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [1, 2])) { // 1 pour Admin, 2 pour Organisateur
    // Rediriger vers la page de connexion ou afficher un message d'erreur
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}
// Débogage - Vérification des données POST reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Données POST reçues: " . print_r($_POST, true)); // Affiche les données envoyées en POST
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);
    $action = $_POST['action'];

    if ($action === 'valider') {
        mysqli_query($conn, "UPDATE events SET status='validé' WHERE id = $eventId");
        header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Tournoi validé.");
        exit;
    } elseif ($action === 'refuser') {
        mysqli_query($conn, "UPDATE events SET status='refusé' WHERE id = $eventId");
        header("Location: https://esportify.alwaysdata.net/frontend/gestion_admin.php?success=Tournoi refusé.");
        exit;
    } else {
        echo json_encode(['error' => 'Action invalide']);
        exit;
    }
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
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Déboguer l'action pour vérifier sa valeur
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    echo json_encode(['error' => 'Aucune action spécifiée']);
    exit;
} else {
    echo json_encode(['info' => 'Action: ' . ($_GET['action'] ?? $_POST['action']) ]);
}

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $event_date = $_POST['event_date'];
            $status = 'en attente'; // L'événement est en attente de validation initiale
            $created_by = $_SESSION['user']['id'];

            // Validation des données
            $error = validateEventData($title, $description, $event_date);
            if ($error) {
                echo json_encode(['error' => $error]);
                exit;
            }

            $sql = "INSERT INTO events (title, description, event_date, status, created_by, created_at)
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
            if ($_SESSION['user']['role'] == 2) { // Organisateur
                // Vérifier que l'événement a été créé par l'utilisateur connecté
                $res = mysqli_query($conn, "SELECT created_by, status FROM events WHERE id = $id");
                $event = mysqli_fetch_assoc($res);
                if ($event['created_by'] !== $_SESSION['user']['id']) {
                    echo json_encode(['error' => 'Vous n\'êtes pas autorisé à modifier cet événement']);
                    exit;
                }

                // Vérifier que l'événement est encore en attente de validation
                if ($event['status'] !== 'En attente') {
                    echo json_encode(['error' => 'Cet événement ne peut pas être modifié']);
                    exit;
                }
            }

            // Mise à jour de l'événement
            $sql = "UPDATE events SET
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

    case 'valider_par_organisateur':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);

            // Vérifier que l'événement est en attente
            $res = mysqli_query($conn, "SELECT status FROM events WHERE id = $id");
            $event = mysqli_fetch_assoc($res);
            if ($event['status'] !== 'En attente') {
                echo json_encode(['error' => 'L\'événement ne peut pas être validé']);
                exit;
            }

            // Vérifier que l'utilisateur est l'organisateur de l'événement
            if ($_SESSION['user']['role'] !== '2') {
                echo json_encode(['error' => 'Seul l\'organisateur peut valider cet événement']);
                exit;
            }

            // Valider l'événement par l'organisateur
            $sql = "UPDATE events SET status = 'validé_organisateur' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => 'Événement validé par l\'organisateur']);
            } else {
                echo json_encode(['error' => 'Erreur lors de la validation']);
            }
        }
        break;

    case 'valider_par_admin':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);

            // Vérifier que l'événement a bien été validé par l'organisateur
            $res = mysqli_query($conn, "SELECT status FROM events WHERE id = $id");
            $event = mysqli_fetch_assoc($res);
            if ($event['status'] !== 'validé_organisateur') {
                echo json_encode(['error' => 'L\'événement doit être validé par l\'organisateur avant d\'être validé par l\'admin']);
                exit;
            }

            // Valider l'événement par l'admin
            if ($_SESSION['user']['role'] !== '1') {
                echo json_encode(['error' => 'Seul l\'admin peut confirmer cette validation']);
                exit;
            }

            // Confirmation de la validation par l'admin
            $sql = "UPDATE events SET status = 'Validé' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => 'Événement validé par l\'admin']);
            } else {
                echo json_encode(['error' => 'Erreur lors de la confirmation']);
            }
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);

            // Vérifier les permissions : L'Admin peut tout faire, l'Organisateur peut supprimer ses propres événements.
            if ($_SESSION['user']['role'] !== '1') {
                // Vérifier que l'événement a été créé par l'utilisateur connecté
                $res = mysqli_query($conn, "SELECT created_by FROM events WHERE id = $id");
                $event = mysqli_fetch_assoc($res);
                if ($event['created_by'] !== $_SESSION['user']['id']) {
                    echo json_encode(['error' => 'Vous n\'êtes pas autorisé à supprimer cet événement']);
                    exit;
                }
            }

            $sql = "DELETE FROM events WHERE id = $id";
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
