<?php
include_once("../db.php");

$message = "";

if (isset($_POST['submit'])) {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_event = $_POST['date_event'];
    $nb_joueurs = isset($_POST['nb_joueurs']) ? (int) $_POST['nb_joueurs'] : 0;
    $created_by = $_SESSION['user']['id'] ?? 0;

    // Validation
    if (empty($titre) || empty($description) || empty($date_event)) {
        $message = "<p style='color:red;'>❌ Tous les champs sont obligatoires.</p>";
    } elseif (strtotime($date_event) < strtotime(date("Y-m-d"))) {
        $message = "<p style='color:red;'>❌ La date de l'événement doit être ultérieure à aujourd'hui.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, status, nb_max_participants, created_by) VALUES (?, ?, ?, 'en attente', ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssii", $titre, $description, $date_event, $nb_joueurs, $created_by);
            if ($stmt->execute()) {
                $message = "<p style='color:green;'>✅ Événement ajouté avec succès !</p>";
            } else {
                $message = "<p style='color:red;'>❌ Erreur lors de l'ajout de l'événement : " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            $message = "<p style='color:red;'>❌ Erreur de préparation de la requête pour l’ajout de l’événement.</p>";
        }
    }
}

// Fonction pour récupérer tous les événements
function getAllEvents($conn) {
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $result = mysqli_query($conn, $sql);

    $events = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
    }
    return $events;
}

// Traitement de l'inscription/désinscription
if (isset($_POST['desinscription_event']) || isset($_POST['inscription_event'])) {
    $event_id = (int) $_POST['event_id'];
    $user_id = $_SESSION['user']['id'];

    if (isset($_POST['desinscription_event'])) {
        $stmt = $conn->prepare("DELETE FROM inscriptions WHERE user_id = ? AND event_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $event_id);
            if ($stmt->execute()) {
                $message = "<p style='color:orange;'>🚫 Désinscription réussie.</p>";
            } else {
                $message = "<p style='color:red;'>❌ Erreur lors de la désinscription : " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            $message = "<p style='color:red;'>❌ Erreur de préparation de la requête pour la désinscription.</p>";
        }

    } elseif (isset($_POST['inscription_event'])) {
        // Vérification si déjà inscrit
        $stmt = $conn->prepare("SELECT id FROM inscriptions WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $checkInscription = $stmt->get_result();
        $stmt->close();

        if ($checkInscription->num_rows == 0) {
            // Récupère le nombre max de participants
            $stmt = $conn->prepare("SELECT nb_max_participants FROM events WHERE id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $eventInfo = $stmt->get_result();
            $stmt->close();

            if ($eventInfo && $eventInfo->num_rows > 0) {
                $max = (int) $eventInfo->fetch_assoc()['nb_max_participants'];

                // Compte les inscrits actuels
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE event_id = ?");
                $stmt->bind_param("i", $event_id);
                $stmt->execute();
                $resCount = $stmt->get_result();
                $nbInscrits = (int) $resCount->fetch_assoc()['total'];
                $stmt->close();

                if ($nbInscrits >= $max) {
                    $message = "<p style='color:red;'>❌ Nombre maximum de participants atteint.</p>";
                } else {
                    $date_inscription = date('Y-m-d H:i:s');
                    $statut = 'en attente';

                    $stmt = $conn->prepare("INSERT INTO inscriptions (user_id, event_id, date_inscription, status) VALUES (?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("iiss", $user_id, $event_id, $date_inscription, $statut);
                        if ($stmt->execute()) {
                            $message = "<p style='color:green;'>✅ Inscription envoyée !</p>";
                        } else {
                            $message = "<p style='color:red;'>❌ Erreur lors de l’inscription : " . $stmt->error . "</p>";
                        }
                        $stmt->close();
                    } else {
                        $message = "<p style='color:red;'>❌ Erreur de préparation de la requête.</p>";
                    }
                }
            } else {
                $message = "<p style='color:red;'>❌ Événement non trouvé.</p>";
            }
        } else {
            $message = "<p style='color:orange;'>⚠️ Vous êtes déjà inscrit à cet événement.</p>";
        }
    }
}
