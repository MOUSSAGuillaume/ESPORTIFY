<?php
include_once("../db.php");

// Traitement du formulaire événement
$message = "";
if (isset($_POST['submit'])) {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_event = $_POST['date_event'];
   // $nb_joueurs = (int) $_POST['nb_joueurs'];//

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
        // Désinscription
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
        // Inscription
        $checkInscription = mysqli_query($conn, "SELECT * FROM inscriptions WHERE user_id = $user_id AND event_id = $event_id");
        if (mysqli_num_rows($checkInscription) == 0) {
            // Vérifie que le nombre max de joueurs n'est pas atteint
            $eventInfo = mysqli_query($conn, "SELECT nb_max_participants FROM events WHERE id = $event_id");
            if ($eventInfo && mysqli_num_rows($eventInfo) > 0) {
                $max = (int) mysqli_fetch_assoc($eventInfo)['nb_max_participants'];

                $resCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM inscriptions WHERE event_id = $event_id");
                $nbInscrits = (int) mysqli_fetch_assoc($resCount)['total'];

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
            }
        } else {
            $message = "<p style='color:orange;'>⚠️ Vous êtes déjà inscrit à cet événement.</p>";
        }
    }
}