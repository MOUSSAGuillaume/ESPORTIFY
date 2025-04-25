<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un événement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        form {
            max-width: 400px;
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <h2>➕ Ajouter un événement</h2>

    <form method="POST">
        <label>Titre :</label>
        <input type="text" name="titre" required>

        <label>Description :</label>
        <textarea name="description" required></textarea>

        <label>Date de l'événement :</label>
        <input type="date" name="date_event" required>

        <button type="submit" name="submit">Ajouter</button>
    </form>

    <?php
if (isset($_POST['submit'])) {
    // Connexion à la base de données
    include_once("../db.php");

    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_event = $_POST['date_event'];

    // Insertion des données dans la base
    $sql = "INSERT INTO evenements (titre, description, date_event)
            VALUES ('$titre', '$description', '$date_event')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Événement ajouté avec succès !</p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur : " . mysqli_error($conn) . "</p>";
    }

    mysqli_close($conn);
}
?>

</body>
</html>
